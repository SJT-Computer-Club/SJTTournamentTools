<?php

namespace Psycle\SJTTournamentTools;

use pocketmine\scheduler\PluginTask;

/**
 * Background tasks
 */
class EverySecondTask extends PluginTask {
    private $running = false;

    private $plugin = null;

    /**
     * Constructor
     *
     * @param pocketmine\plugin\PluginBase $plugin
     */
    public function __construct($plugin) {
        parent::__construct($plugin);
        $this->plugin = $plugin;
    }

    /**
     * Called every time the task is triggered
     *
     * @param int $currentTick The value of the current tick (1 tick = 1/20th s)
     */
    function onRun($currentTick) {
        if ($this->running) {
            $this->plugin->getLogger()->info('Skipped EverySecondTask, already running');
            return;
        }

        $this->running = true;
        $this->doTasks();
        $this->running = false;
    }

    /**
     * Perform the tasks
     */
    private function doTasks() {
        $this->plugin->getGameManager()->tick();
    }
}
