<?php

namespace Psycle\SJTTournamentTools\Game;

use pocketmine\block\Air;
use pocketmine\block\Quartz;
use pocketmine\math\Vector3;
use pocketmine\Server;
use Psycle\SJTTournamentTools\SJTTournamentTools;

/**
 * Description of Parkour
 *
 * @author austin
 */
class Build extends Game {
    /**
     * Constructor.  Clears the build area and teleports all players to the start location.
     *
     * @param array $config The configuration array for the game (from the config file)
     */
    public function __construct($config) {
        parent::__construct($config);

        $plugin = SJTTournamentTools::getInstance();
        $plugin->getLocationManager()->teleportPlayersToGrid('Build', $plugin->getPlayers(), 16, 5);
        $this->clearBuildArea();
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

		$lengthx = 64;
		$lengthy = 100;
		$lengthz = 80;

		for ($x = 0; $x < $lengthx; $x++) {
			for ($y = 0; $y < $lengthy; $y++) {
				for ($z = 0; $z < $lengthz; $z++) {
					$newx = $location['x'] + $x + 2;
					$newy = $location['y'] + $y + 1;
					$newz = $location['z'] + $z - 1;
					$level->setBlock(new Vector3($newx, $newy, $newz), new Air(), false, false);
				}
			}
		}

		for ($x = 0; $x < $lengthx; $x++) {
			for ($z = 0; $z < $lengthz; $z++) {
				$newx = $location['x'] + $x + 2;
				$newy = $location['y'];
				$newz = $location['z'] + $z - 1;
				if (!(($x + 1) % 16) || !(($z + 1) % 16)) {
					$level->setBlock(new Vector3($newx, $newy, $newz), new Air(), false, false);
				} else {
					$level->setBlock(new Vector3($newx, $newy, $newz), new Quartz(), false, false);
				}
			}
		}
    }
}
