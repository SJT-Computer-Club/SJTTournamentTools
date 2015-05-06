<?php

namespace Psycle\SJTTournamentTools\Game;

use pocketmine\block\Air;
use pocketmine\block\Gold;
use pocketmine\math\Vector3;
use pocketmine\Server;
use Psycle\SJTTournamentTools\SJTTournamentTools;

/**
 * Description of Parkour
 *
 * @author austin
 */
class TreasureHunt extends Game {
    /**
     * Constructor.  Distributes the treasure blocks and teleports all players to the start location.
     *
     * @param array $config The configuration array for the game (from the config file)
     */
    public function __construct($config) {
        parent::__construct($config);

        $plugin = SJTTournamentTools::getInstance();
        $plugin->getLocationManager()->teleportPlayersToCircle('TreasureHunt', $plugin->getPlayers());
        $this->distributeBlocks();
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
}
