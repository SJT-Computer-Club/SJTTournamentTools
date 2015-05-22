<?php

namespace Psycle\SJTTournamentTools;

use pocketmine\block\Lava;
use pocketmine\block\TNT;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Bucket;
use Psycle\SJTTournamentTools\Game\Archery;
use Psycle\SJTTournamentTools\Game\Build;
use Psycle\SJTTournamentTools\Game\Game;
use Psycle\SJTTournamentTools\Game\Parkour;
use Psycle\SJTTournamentTools\Game\TreasureHunt;
use ReflectionClass;

/**
 * Manages Games
 *
 * @author austin
 */
class GameManager {

    /** Types of game */
    const GAME_TYPE_ARCHERY = 'Archery',
          GAME_TYPE_BUILD = 'Build',
          GAME_TYPE_PARKOUR = 'Parkour',
          GAME_TYPE_TREASUREHUNT = 'TreasureHunt';

    /** @var Game The currently active game, null if no game active. */
    private $currentGame = null;

    /** @var array Configuration array */
    private $config = null;

    /**
     * Constructor
     *
     * @param array $config Array containing games from config file
     */
    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * Create a new game of a given type
     *
     * @param string $gameType GAME_TYPE_BUILD | GAME_TYPE_PARKOUR | GAME_TYPE_TREASUREHUNT
     * @return bool true if successful
     */
    public function setupGame($gameType) {
        if ($this->currentGame !== null && $this->currentGame->isRunning()) {
            return false;
        }

        switch ($gameType) {
            case self::GAME_TYPE_ARCHERY:
                $this->currentGame = new Archery($this->config['Archery']);
                break;
            case self::GAME_TYPE_BUILD:
                $this->currentGame = new Build($this->config['Build']);
                break;
            case self::GAME_TYPE_PARKOUR:
                $this->currentGame = new Parkour($this->config['Parkour']);
                break;
            case self::GAME_TYPE_TREASUREHUNT:
                $this->currentGame = new TreasureHunt($this->config['TreasureHunt']);
                break;
        }

        return true;
    }

    /**
     * Returns the current game type, or null if none.
     *
     * @return string null | GAME_TYPE_BUILD | GAME_TYPE_PARKOUR | GAME_TYPE_TREASUREHUNT
     */
    public function getCurrentGameType() {
        if ($this->currentGame == null) {
            return null;
        }

        $reflect = new ReflectionClass($this->currentGame);
        return $reflect->getShortName();
    }

    /**
     * Start the game
     */
    public function startGame() {
        if ($this->currentGame !== null) {
            $this->currentGame->start();
        }

        return true;
    }

    /**
     * Stop the game
     */
    public function stopGame() {
        if ($this->currentGame !== null) {
            $this->currentGame->stop();
        }

        $this->currentGame->displayScores();

        return true;
    }

    /**
     * Tick the current game. Called every second.
     */
    public function tick() {
        if ($this->currentGame !== null) {
            $this->currentGame->tick();
        }
    }

    /**
     * Block break event handling
     *
     * @param BlockBreakEvent $event The event
     */
    public function blockBreakEvent(BlockBreakEvent $event) {
        if ($this->currentGame == null || !$this->currentGame->isRunning()) {
            // If there is no current game, disallow block breaking for normal users
            if (!$event->getPlayer()->isOp()) {
                $event->setCancelled();
            }
            return;
        }

        $this->currentGame->blockBreakEvent($event);
    }

    /**
     * Block place event handling
     *
     * @param BlockPlaceEvent $event The event
     */
    public function blockPlaceEvent(BlockPlaceEvent $event) {
        $block = $event->getBlock();

        // No-one can place dangerous block types, not even ops!
        if ($block instanceof Lava || $block instanceof TNT) {
            $event->setCancelled();
            return;
        }

        // If there is no current game, disallow block placing for normal users
        if ($this->currentGame == null || !$this->currentGame->isRunning()) {
            if (!$event->getPlayer()->isOp()) {
                $event->setCancelled();
            }
            return;
        }

        $this->currentGame->blockPlaceEvent($event);
    }

    /**
     * Player Interaction event handling
     *
     * @param PlayerInteractEvent $event The event
     * @return type
     */
    public function playerInteractEvent(PlayerInteractEvent $event) {
        $item = $event->getItem();

        // No-one can use buckets (water, lava), not even ops!
        if ($item instanceof Bucket) {
            $event->setCancelled();
            return;
        }

        // If there is no current game, disallow interaction for normal users
        if ($this->currentGame == null || !$this->currentGame->isRunning()) {
            if (!$event->getPlayer()->isOp()) {
                $event->setCancelled();
            }
            return;
        }

        $this->currentGame->playerInteractEvent($event);
    }

    /**
     * Damage event handling
     *
     * @param EntityDamageEvent $event The event
     */
    public function entityDamageEvent(EntityDamageEvent $event) {
        // Disallow all damage
        $event->setCancelled();
    }

    /**
     * Projectile Hit event handling
     *
     * @param PlayerInteractEvent $event The event
     * @return type
     */
    public function projectileHitEvent(ProjectileHitEvent $event) {
        if ($this->currentGame == null || !$this->currentGame->isRunning()) {
            return;
        }

        $this->currentGame->projectileHitEvent($event);
    }
}
