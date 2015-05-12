<?php

namespace Psycle\SJTTournamentTools\Game;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\Gold;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;
use Psycle\SJTTournamentTools\SJTTournamentTools;

/**
 * Description of Parkour
 *
 * @author austin
 */
class TreasureHunt extends Game {
    /** @var array Player scores */
    private $scores = null;

    /**
     * Constructor.  Distributes the treasure blocks and teleports all players to the start location.
     *
     * @param array $config The configuration array for the game (from the config file)
     */
    public function __construct($config) {
        parent::__construct($config);

        $this->scores = array();

        $plugin = SJTTournamentTools::getInstance();
        $plugin->getLocationManager()->teleportPlayersToCircle('TreasureHunt', $plugin->getPlayers());
        $this->distributeBlocks();
        $this->setGameMode(Player::ADVENTURE);
    }

    /**
     * Start the game.
     */
    public function start() {
        parent::start();
    }

    /**
     * Stop the game.  Clear all treasure blocks to ensure no-one can score after the game has finished.
     */
    public function stop() {
        parent::stop();

        $this->clearBlocks();
    }

    public function getStatus() {

    }

    /**
     * Randomly assign blocks to all locations.
     */
    private function distributeBlocks() {
        $plugin = SJTTournamentTools::getInstance();
		$level = Server::getInstance()->getDefaultLevel();
		$locations = $plugin->getLocationManager()->getLocations();

		foreach ($locations as $k => $v) {
			if (rand(0, 10) < 5) {
                $plugin->getLogger()->info('Treasure at ' . $k . ' ' . $v['x'] . ',' . $v['y'] . ',' . $v['z']);
				$level->setBlock(new Vector3($v['x'], $v['y'], $v['z']), new Gold(), false, false);
			} else {
                $plugin->getLogger()->info('Air at ' . $k . ' ' . $v['x'] . ',' . $v['y'] . ',' . $v['z']);
				$level->setBlock(new Vector3($v['x'], $v['y'], $v['z']), new Air(), false, false);
            }
		}
	}

    /**
     * Remove all treasure blocks
     */
    private function clearBlocks() {
        $plugin = SJTTournamentTools::getInstance();
		$level = Server::getInstance()->getDefaultLevel();
		$locations = $plugin->getLocationManager()->getLocations();

		foreach ($locations as $k => $v) {
            $level->setBlock(new Vector3($v['x'], $v['y'], $v['z']), new Air(), true, true);
 		}
    }

    /**
     * Handle a PlayerInteractEvent
     *
     * @param PlayerInteractEvent $event The Event
     */
    public function playerInteractEvent(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        $block = $event->getBlock();

        if ($block instanceof Gold) {
            // Handle player scoring
            $this->playerScored($player, $block);
        }
    }

    /**
     * The player has scored
     *
     * @param Player $player The Player
     * @param Block $block The Block the player has scored
     */
    private function playerScored(Player $player, Block $block) {
        $score = 10;
        $playerName = $player->getName();
        $scoreKey = $block->getX() . '-' . $block->getY() . '-' . $block->getZ();

        if (!array_key_exists($playerName, $this->scores)) {
            $this->scores[$playerName] = array();
        }

        if (!array_key_exists($scoreKey, $this->scores[$playerName])) {
            $this->scores[$playerName][$scoreKey] = $score;
            SJTTournamentTools::getInstance()->getServer()->broadcastMessage($playerName . ' scored ' . $score);
        }
    }

    /**
     * Display all scores for this game
     */
    public function displayScores() {
        SJTTournamentTools::getInstance()->getServer()->broadcastMessage('Scores for recent game of Treasure Hunt are...');

        foreach ($this->scores as $playerName => $playerScores) {
			$score = 0;
			foreach ($playerScores as $location => $value) {
				$score += $value;
			}

            SJTTournamentTools::getInstance()->getServer()->broadcastMessage($playerName . ' scored ' . $score);
		}
    }

}
