<?php

namespace Psycle\SJTTournamentTools\Game;

use pocketmine\block\Air;
use pocketmine\block\Quartz;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;
use Psycle\SJTTournamentTools\SJTTournamentTools;

/**
 * Description of Parkour
 *
 * @author austin
 */
class Build extends Game {
    /** @var int The width of each square platform */
    const PLATFORM_WIDTH = 15;
    /** @var int The number of platforms in the x dimension */
    const PLATFORM_COUNT_X = 2;
    /** @var int The number of platforms in the z dimension */
    const PLATFORM_COUNT_Z = 5;
    /** @var int The number of blocks to clear above the platforms */
    const AIR_CLEARANCE = 100;

    /**
     * Constructor.  Clears the build area and teleports all players to the start location.
     *
     * @param array $config The configuration array for the game (from the config file)
     */
    public function __construct($config) {
        parent::__construct($config);

        $plugin = SJTTournamentTools::getInstance();
        $plugin->getLocationManager()->teleportPlayersToGrid('Build', $plugin->getPlayers(), self::PLATFORM_WIDTH + 1, self::PLATFORM_COUNT_Z);
        $this->clearBuildArea();
        $this->setGameMode(Player::CREATIVE);
    }

    /**
     * Start the game.
     */
    public function start() {
        parent::start();
    }

    /**
     * Stop the game.
     */
    public function stop() {
        parent::stop();
    }

    public function getStatus() {

    }

    /**
     * Clear the game area.  Fill the space above the plinths with air but don't delete the signs.
     */
    private function clearBuildArea() {
        $plugin = SJTTournamentTools::getInstance();
		$level = Server::getInstance()->getDefaultLevel();
		$location = $plugin->getLocationManager()->getLocation('Build');

		$lengthx = self::PLATFORM_COUNT_X * (self::PLATFORM_WIDTH + 1);
		$lengthz = self::PLATFORM_COUNT_Z * (self::PLATFORM_WIDTH + 1);

        // TEMP clear large area 4 x 5 squares
        /*for ($x = -5; $x < self::PLATFORM_WIDTH * 4 + 5; $x++) {
			for ($z = -5; $z < self::PLATFORM_WIDTH * 5 + 5; $z++) {
				$newx = $location['x'] + $x;
				$newy = $location['y'];
				$newz = $location['z'] + $z;

				$level->setBlock(new Vector3($newx, $newy, $newz), new Air(), false, false);
			}
		}*/

        // Clear area above platforms
		for ($x = 0; $x < $lengthx; $x++) {
			for ($y = 0; $y < self::AIR_CLEARANCE; $y++) {
				for ($z = 0; $z < $lengthz; $z++) {
					$newx = $location['x'] + $x;
					$newy = $location['y'] + $y + 1;
					$newz = $location['z'] + $z;
					$level->setBlock(new Vector3($newx, $newy, $newz), new Air(), false, false);
				}
			}
		}

        // Rebuild platforms and gaps between
		for ($x = 0; $x < $lengthx; $x++) {
			for ($z = 0; $z < $lengthz; $z++) {
				$newx = $location['x'] + $x;
				$newy = $location['y'];
				$newz = $location['z'] + $z;
				if (!($x % (self::PLATFORM_WIDTH + 1)) || !($z % (self::PLATFORM_WIDTH + 1))) {
					$level->setBlock(new Vector3($newx, $newy, $newz), new Air(), false, false);
				} else {
					$level->setBlock(new Vector3($newx, $newy, $newz), new Quartz(), false, false);
				}
			}
		}
    }

    /**
     * Handle a BlockBreakEvent
     *
     * @param BlockBreakEvent $event The Event
     */
    public function blockBreakEvent(BlockBreakEvent $event) {
        $this->checkBlockEvent($event);
    }

    /**
     * Handle a BlockPlaceEvent
     *
     * @param BlockPlaceEvent $event The Event
     */
    public function blockPlaceEvent(BlockPlaceEvent $event) {
        $this->checkBlockEvent($event);
    }

    /**
     * Display the scores for the game - in this case just show a message about judging
     */
    public function displayScores() {
        SJTTournamentTools::getInstance()->getServer()->broadcastMessage('Please wait while the judges decide on the scores...');
    }

    /**
     * Check whether block actions are allowed.  Players are only allowed to build inside their area while the game is
     * running.
     *
     * @param BlockEvent $event The Event
     * @return type
     */
    private function checkBlockEvent(BlockEvent $event) {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $plugin = SJTTournamentTools::getInstance();
        $playerNumber = array_search($player->getName(), $plugin->getPlayers());

        // If the player isn't in the players list then disallow block breaking
        if ($playerNumber === FALSE) {
            if (!$player->isOp()) {
                $event->setCancelled();
            }
            return;
        }

        $location = $plugin->getLocationManager()->getLocation('Build');
        $xmin = $location['x'] + 1 + ((int)($playerNumber / self::PLATFORM_COUNT_Z) * (self::PLATFORM_WIDTH + 1));
        $xmax = $xmin + self::PLATFORM_WIDTH - 1;
        $zmin = $location['z'] + 1 + (($playerNumber % self::PLATFORM_COUNT_Z) * (self::PLATFORM_WIDTH + 1));
        $zmax = $zmin + self::PLATFORM_WIDTH - 1;

        if ($block->getX() < $xmin || $block->getX() > $xmax || $block->getZ() < $zmin || $block->getZ() > $zmax) {
            $event->setCancelled();
        }
    }
}
