<?php

namespace Psycle\SJTTournamentTools;

use pocketmine\level\Level;
use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use pocketmine\math\Vector3;

/**
 * Background tasks
 */
class EveryMinuteTask extends PluginTask {
    private $running = false;

    /**
     * Called every time the task is triggered
     *
     * @param int $currentTick The value of the current tick (1 tick = 1/20th s)
     */
    function onRun($currentTick) {
        $plugin = SJTTournamentTools::getInstance();

        if ($this->running) {
            $plugin->getLogger()->info('Skipped EveryMinuteTask');
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
        $level = Server::getInstance()->getDefaultLevel();

        // Keep it day all the time
        $level->setTime(6000);

        // Reset spawn point for all players
        $plugin = SJTTournamentTools::getInstance();
        $spawnPoint = $plugin->getSpawnPoint();

        foreach ($plugin->getPlayers() as $playerName) {
            $player = $plugin->getServer()->getPlayer($playerName);

            if (!$player) {
                continue;
            }

            $player->setSpawn($spawnPoint);
        }
    }
}
