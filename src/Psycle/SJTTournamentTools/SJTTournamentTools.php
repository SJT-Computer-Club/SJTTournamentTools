<?php

namespace Psycle\SJTTournamentTools;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

use Psycle\SJTTournamentTools\Game\Parkour;
use Psycle\SJTTournamentTools\LocationManager;

/**
 * Main plugin class
 */
class SJTTournamentTools extends PluginBase implements Listener {

    /**
     * A static reference to this plugin instance
     * @var SJTMapTools
     */
    private static $instance;

    /**
     * Instance of LocationManager to handle locations
     *
     * @var type Psycle\SJTTournamentTools\LocationManager
     */
    private $locationManager;


    /**
     * Called when the plugin is enabled
     */
    public function onEnable() {
        self::$instance = $this;

        $this->getLogger()->info('Plugin Enabled');

        $this->initConfig();
        $this->initDataFolder();

        $this->locationManager = new LocationManager($this->getConfig()->get('locations'));
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
     * @return SJTMapTools The plugin instance
     */
    public static function getInstance() {
        return self::$instance;
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
            case 'tptolocation':
                $this->getLogger()->info($sender->getName() . ' called tptolocation');
                return $this->tpToLocation($sender, $args);
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
            $sender->sendMessage('Please supply a location name');
            $this->getLogger()->info('tptolocation failed, ' . $sender->getName() . ' did not specify a location name');
            return false;
        }

        $player = $this->getServer()->getPlayer($args[0]);

        if (!$player) {
            $sender->sendMessage('The player "' . $sender->getName() . '" doesn\'t exist');
            return false;
        }

        $this->locationManager->teleportToLocation($player, $args[1]);

        return true;
    }
}
