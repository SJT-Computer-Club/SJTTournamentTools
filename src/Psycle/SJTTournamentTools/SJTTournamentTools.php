<?php

namespace Psycle\SJTTournamentTools;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use Psycle\SJTTournamentTools\GameManager;
use Psycle\SJTTournamentTools\LocationManager;

/**
 * Main plugin class
 */
class SJTTournamentTools extends PluginBase implements Listener {

    /** @var SJTMapTools A static reference to this plugin instance */
    private static $instance;

    /** @var LocationManager Instance of LocationManager to handle locations */
    private $locationManager;

    /** @var GameManager Instance of Game manager to handle games */
    private $gameManager;

    /** @var array Array of player names */
    private $players = array();

    /** @var Vector3 The default spawn point for all players */
    private $spawnPoint;

    /**
     * Called when the plugin is enabled
     */
    public function onEnable() {
        self::$instance = $this;

        $this->getLogger()->info('Plugin Enabled');

        $this->initConfig();
        $this->initDataFolder();

        $config = $this->getConfig();

        $this->locationManager = new LocationManager($config->get('locations'));
        $this->gameManager = new GameManager($config->get('games'));
        $this->players = $config->get('players');
        $this->spawnPoint = new Vector3($config->get('spawnpoint')['x'], $config->get('spawnpoint')['y'], $config->get('spawnpoint')['z']);

        // Register repeating actions
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new EveryMinuteTask($this), 60 * 20);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new EverySecondTask($this), 1 * 20);

        // Listen for events
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    /**
     * Called when the plugin is disabled
     */
    public function onDisable() {
        $this->getConfig()->set('locations', $this->locationManager->getLocations());
        $this->getConfig()->save();
        $this->getLogger()->info('Plugin Disabled');
    }

    /**
     * Returns the plugin instance
     * @return SJTTournamentTools The plugin instance
     */
    public static function getInstance() {
        return self::$instance;
    }

    /**
     * Get the LocationManager
     *
     * @return LocationManager
     */
    public function getLocationManager() {
        return $this->locationManager;
    }

    /**
     * Get the GameManager
     *
     * @return GameManager
     */
    public function getGameManager() {
        return $this->gameManager;
    }

    /**
     * Get the array of player names
     *
     * @return array
     */
    public function getPlayers() {
        return $this->players;
    }

    /**
     * Get the default spawn point
     *
     * @return Vector3
     */
    public function getSpawnPoint() {
        return $this->spawnPoint;
    }

    /* Data handling */

    /**
     * Load the default config, ensure it is saved to config override, load
     * values
     */
    private function initConfig() {
        // Take the default config from [plugin folder]/resources/config.yml
        // and save it to [data folder]/config.yml if the file doesn't exist
        $this->saveDefaultConfig();
    }

    /**
     * Create the data folder structure
     */
    private function initDataFolder() {
        $dataFolder = $this->getDataFolder();
        if (!is_dir($dataFolder)) {
            $this->getLogger()->info('Data folder not found, creating at: ' . $dataFolder);
            mkdir($dataFolder, 0755, true);
        }
    }

    /* Command handling */

    /**
     * Handle a command from a player
     *
     * @param CommandSender $sender The command sender object
     * @param Command $command The command object
     * @param type $label
     * @param array $args The command arguments
     * @return boolean true if successful
     */
    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        switch (strtolower($command->getName())) {
            case 'addlocation':
                $this->getLogger()->info($sender->getName() . ' called addlocation');
                return $this->addLocation($sender, $args);
            case 'tu':
                $this->getLogger()->info($sender->getName() . ' called tu');
                return $this->tpToLocation($sender, $args);
//            case 'archery':
//                $this->getLogger()->info($sender->getName() . ' called archery');
//                return $this->gameManager->setupGame(GameManager::GAME_TYPE_ARCHERY);
            case 'build':
                $this->getLogger()->info($sender->getName() . ' called build');
                return $this->gameManager->setupGame(GameManager::GAME_TYPE_BUILD);
            case 'parkour':
                $this->getLogger()->info($sender->getName() . ' called parkour');
                return $this->gameManager->setupGame(GameManager::GAME_TYPE_PARKOUR);
            case 'treasurehunt':
                $this->getLogger()->info($sender->getName() . ' called treasurehunt');
                return $this->gameManager->setupGame(GameManager::GAME_TYPE_TREASUREHUNT);
            case 'gamestart':
                $this->getLogger()->info($sender->getName() . ' called gamestart');
                return $this->gameManager->startGame();
            case 'gamestop':
                $this->getLogger()->info($sender->getName() . ' called gamestop');
                return $this->gameManager->stopGame();
       }

        return false;
    }

    /**
     * Adds a location.
     *
     * @param CommandSender $sender The command sender object
     * @param array $args The arguments passed to the command
     * @return boolean true if successful
     */
    private function addLocation(CommandSender $sender, array $args) {
        $player = $this->getServer()->getPlayer($sender->getName());

        if (!$player) {
            $sender->sendMessage('The player "' . $sender->getName() . '" doesn\'t exist.  Are you trying to run addlocation from the console?');
            return false;
        }

        if (!isset($args[0]) || $args[0] == '') {
            $sender->sendMessage('Please supply a location name');
            $this->getLogger()->info('addlocation failed, ' . $sender->getName() . ' did not specify a location name');
            return false;
        }

        $this->locationManager->addLocation($args[0], $player->x, $player->y, $player->z);
        $sender->sendMessage('Location ' . $args[0] . '[' . $player->x . ',' . $player->y . ',' . $player->z . '] added');

        return true;
    }

    /**
     * Teleport a named player to a named location
     *
     * @param CommandSender $sender The command sender object
     * @param array $args The arguments passed to the command
     * @return boolean true if successful
     */
    private function tpToLocation(CommandSender $sender, array $args) {
        if (!isset($args[0]) || $args[0] == '') {
            $sender->sendMessage('Please supply a player name');
            $this->getLogger()->info('tptolocation failed, ' . $sender->getName() . ' did not specify a player name');
            return false;
        }

        if (!isset($args[1]) || $args[1] == '') {
            if ($this->gameManager->getCurrentGameType() !== null) {
                $args[1] = $this->gameManager->getCurrentGameType();
            } else {
                $sender->sendMessage('There is no currently active game, please supply a location name');
                $this->getLogger()->info('tptolocation failed, ' . $sender->getName() . ' did not specify a location name and there is no current game');
                return false;
            }
        }

        if ($this->locationManager->getLocation($args[1]) == null) {
            $sender->sendMessage('Location ' . $args[1] . ' doesn\'t exist');
            $this->getLogger()->info('tptolocation failed, ' . $sender->getName() . ' specified a location ' . $args[1] . ' which doesn\'t exist');
            return false;
        }

        $this->locationManager->teleportPlayerToLocation($args[1], $args[0]);
        $sender->sendMessage('Teleported ' . $args[0] . ' to ' . $args[1]);

        return true;
    }

    /**
     * Handle BlockBreakEvent.
     *
     * @param BlockBreakEvent $event The event
     *
     * @priority       NORMAL
     * @ignoreCanceled false
     */
    public function onBlockBreak(BlockBreakEvent $event) {
        $this->gameManager->blockBreakEvent($event);
    }

    /**
     * Handle BlockPlaceEvent.
     *
     * @param BlockPlaceEvent $event The event
     *
     * @priority       NORMAL
     * @ignoreCanceled false
     */
    public function onBlockPlace(BlockPlaceEvent $event) {
        $this->gameManager->blockPlaceEvent($event);
    }

    /**
     * Handle PlayerInteractEvent.
     *
     * @param PlayerInteractEvent $event The event
     *
     * @priority       NORMAL
     * @ignoreCanceled false
     */
    public function onPlayerInteract(PlayerInteractEvent $event) {
        $this->gameManager->playerInteractEvent($event);
    }

    /**
     * Handle EntityDamageEvent.
     *
     * @param EntityDamageEvent $event The event
     *
     * @priority       NORMAL
     * @ignoreCanceled false
     */
    public function onHurt(EntityDamageEvent $event) {
        $this->gameManager->entityDamageEvent($event);
    }

    /**
     * Handle ProjectileHitEvent.
     *
     * @param ProjectileHitEvent $event The event
     *
     * @priority       NORMAL
     * @ignoreCanceled false
     */
    public function onProjectileHit(ProjectileHitEvent $event) {
        $this->gameManager->projectileHitEvent($event);
    }
}
