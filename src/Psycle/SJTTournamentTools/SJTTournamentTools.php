<?php

namespace Psycle\SJTTournamentTools;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

use Psycle\SJTTournamentTools\Game\Parkour;

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
     * Called when the plugin is enabled
     */
    public function onEnable() {
        self::$instance = $this;

        $this->getLogger()->info('Plugin Enabled');

        $this->initConfig();
        $this->initDataFolder();
    }

    /**
     * Called when the plugin is disabled
     */
    public function onDisable() {
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

        $config = $this->getConfig();

        $level = Server::getInstance()->getDefaultLevel();

        //$parkour = new Parkour($config->get('games')['Parkour']);
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
            case 'samplecommand':
                $this->getLogger()->info($sender->getName() . ' called samplecommand');
                return $this->sampleCommand($sender, $args);
        }

        return false;
    }

    /**
     * List all currently defined regions.
     *
     * @param CommandSender $sender The command sender object
     * @param array $args The arguments passed to the command
     * @return boolean true if successful
     */
    private function sampleCommand(CommandSender $sender, array $args) {
        $sender->sendMessage("Sample Command Output");

        return true;
    }
}
