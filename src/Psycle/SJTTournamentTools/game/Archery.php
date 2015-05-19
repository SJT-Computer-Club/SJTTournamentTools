<?php

namespace Psycle\SJTTournamentTools\Game;

use pocketmine\block\Block;
use pocketmine\block\Planks;
use pocketmine\block\Wool;
use pocketmine\entity\Arrow;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\item\Bow;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;
use Psycle\SJTTournamentTools\SJTTournamentTools;

/**
 * Description of Archery
 *
 * @author austin
 */
class Archery extends Game {
    /** @var int The distance to the target */
    const TARGET_DISTANCE_FROM_PLAYERS = 15;
    /** @var int The diameter of the target, should ideally be a multiple of 8 as there are 4 rings outside the center */
    const TARGET_DIAMETER = 8;
    /** @var int The height of the bottom of the target off the ground */
    const TARGET_DISTANCE_FROM_GROUND = 1;

    /** @var array Player scores */
    private $scores = null;

    /**
     * Constructor.  Teleport all players to the start location.
     *
     * @param array $config The configuration array for the game (from the config file)
     */
    public function __construct($config) {
        parent::__construct($config);

        $this->scores = array();

        $plugin = SJTTournamentTools::getInstance();
        $plugin->getLocationManager()->teleportPlayersToLine('Archery', $plugin->getPlayers(), 0);
        $this->setUpTarget();
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

    /**
     * Tick the game, called every second
     *
     * @return boolean true if game is continuing, false if we have ended
     */
    public function tick() {
        $this->ensureBowsActive();

       return parent::tick();
    }

    public function getStatus() {

    }

    private function setUpTarget() {
        $plugin = SJTTournamentTools::getInstance();
        $level = Server::getInstance()->getDefaultLevel();
        $location = $plugin->getLocationManager()->getLocation('Archery');

        // Wooden support
        for ($y = 0; $y < self::TARGET_DIAMETER + self::TARGET_DISTANCE_FROM_GROUND; $y++) {
            $level->setBlock(new Vector3($location['x'], $y, $location['z'] + self::TARGET_DISTANCE_FROM_PLAYERS + 1), new Planks(), false, false);
        }

        // White ring 4
        $points = $this->getFilledCircle($location['x'], self::TARGET_DIAMETER + self::TARGET_DISTANCE_FROM_GROUND, self::TARGET_DIAMETER / 2);
        foreach ($points as $point) {
            $level->setBlock(new Vector3($point['x'], $point['y'], $location['z'] + self::TARGET_DISTANCE_FROM_PLAYERS), new Wool(), false, false);
        }

        // Black ring 3
        $points = $this->getFilledCircle($location['x'], self::TARGET_DIAMETER + self::TARGET_DISTANCE_FROM_GROUND, (self::TARGET_DIAMETER / 2) * (3 / 4));
        foreach ($points as $point) {
            $level->setBlock(new Vector3($point['x'], $point['y'], $location['z'] + self::TARGET_DISTANCE_FROM_PLAYERS), new Wool(15), false, false);
        }

        // Cyan ring 2
        $points = $this->getFilledCircle($location['x'], self::TARGET_DIAMETER + self::TARGET_DISTANCE_FROM_GROUND, (self::TARGET_DIAMETER / 2) * (2 / 4));
        foreach ($points as $point) {
            $level->setBlock(new Vector3($point['x'], $point['y'], $location['z'] + self::TARGET_DISTANCE_FROM_PLAYERS), new Wool(9), false, false);
        }

        // Red ring 1
        $points = $this->getFilledCircle($location['x'], self::TARGET_DIAMETER + self::TARGET_DISTANCE_FROM_GROUND, (self::TARGET_DIAMETER / 2) * (1 / 4));
        foreach ($points as $point) {
            $level->setBlock(new Vector3($point['x'], $point['y'], $location['z'] + self::TARGET_DISTANCE_FROM_PLAYERS), new Wool(14), false, false);
        }

        // Yellow center
        $level->setBlock(new Vector3($location['x'], self::TARGET_DIAMETER + self::TARGET_DISTANCE_FROM_GROUND, $location['z'] + self::TARGET_DISTANCE_FROM_PLAYERS), new Wool(4), false, false);
    }

    /**
     * Ensure that every player has a bow and is using it
     */
    private function ensureBowsActive() {
        $plugin = SJTTournamentTools::getInstance();
        $playerNames = $plugin->getPlayers();


		foreach ($playerNames as $playerName) {
            $player = $plugin->getServer()->getPlayer($playerName);

            if (!$player) {
                continue;
            }

            $inventory = $player->getInventory();
            $bow = new Bow();

            if (!$inventory->contains($bow)) {
                $inventory->addItem($bow);
            }

            if (!$inventory->getItemInHand() instanceof Bow) {
                $inventory->setItemInHand($bow);
            }
		}
    }

    /**
     * Generate a filled circle of points as an array
     *
     * @param int $x x position of center
     * @param int $y y position of center
     * @param int $r radius
     * @return array
     */
    private function getFilledCircle($x, $y, $r) {
        $result = [];

        for ($i = $x - $r; $i <= $x + $r; $i++) {
            for ($j = $y - $r; $j <= $y + $r; $j++) {
                if ($this->getDistance($i, $j, $x, $y) <= $r) {
                    $result[] = ['x' => $i, 'y' => $j];
                }
            }
        }
        return $result;
    }

    /**
     * Calculate the distance between two points
     *
     * @param int $x1 x position of point 1
     * @param int $y1 y position of point 1
     * @param int $x2 x position of point 2
     * @param int $y2 y position of point 2
     * @return float
     */
    private function getDistance($x1, $y1, $x2, $y2) {
        return sqrt(pow($x2 - $x1, 2) + pow($y2 - $y1, 2));
    }


    /**
     * Player interaction event handling
     *
     * @param PlayerInteractEvent $event The Event
     */
    public function playerInteractEvent(PlayerInteractEvent $event) {
    }

    /**
     * Player interaction event handling
     *
     * @param PlayerInteractEvent $event The Event
     */
    public function projectileHitEvent(ProjectileHitEvent $event) {
        $projectile = $event->getEntity();

        if ($projectile === null || !$projectile instanceof Arrow || $projectile->shootingEntity === null) {
            return;
        }

        $plugin = SJTTournamentTools::getInstance();
        $location = $plugin->getLocationManager()->getLocation('Archery');
        $level = Server::getInstance()->getDefaultLevel();

        // Check whether it's in the target
print "z: " . (int)$projectile->z;
print " target z: " . ($location['z'] + self::TARGET_DISTANCE_FROM_PLAYERS);

        $block = $level->getBlock(new Vector3($projectile->x, $projectile->y, $projectile->z));
print "block: " . $block;
        $player = $event->getEntity()->shootingEntity;
print "player: " . $player->getName();
        $distanceFromCentre = $this->getDistance($location['x'], self::TARGET_DIAMETER + self::TARGET_DISTANCE_FROM_GROUND, $projectile->x, $projectile->y);
print "dist: " . $distanceFromCentre;
    }

    /**
     * The player has scored
     *
     * @param Player $player The Player
     * @param Block $block The Block the player has scored
     */
    private function playerScored(Player $player, Block $block) {

    }

    /**
     * Display all scores for this game
     */
    public function displayScores() {

    }
}
