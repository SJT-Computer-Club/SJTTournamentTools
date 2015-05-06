<?php

namespace Psycle\SJTTournamentTools;

use pocketmine\math\Vector3;

/**
 * Handles Locations
 *
 * @author austin
 */
class LocationManager {
    /** @var array The array of locations */
    private $locations = array();

    /**
     * Constructor
     *
     * @param array $config Array containing locations from config file
     */
    public function __construct($config) {
        $this->locations = $config;
    }

    /**
     * Get the current set of locations
     *
     * @return array The array of locations
     */
    public function getLocations() {
        return $this->locations;
    }

    /**
     * Get a location by its title
     *
     * @param string $title The name of the location
     * @return array The location details
     */
    public function getLocation($title) {
        if (!array_key_exists($title, $this->locations)) {
            return null;
        }

        return $this->locations[$title];
    }

    /**
     * Add a location
     *
     * @param string $title The name of the location
     * @param int $x The x coordinate
     * @param int $y The y coordinate
     * @param int $z The z coordinate
     */
    public function addLocation($title, $x, $y, $z) {
        $this->locations[$title] = ['x' => $x, 'y' => $y, 'z' => $z];
    }

    /**
     * Teleport a player to a location
     *
     * @param string $title The title of the location
     * @param Player $playerName The Player name
     * @return boolean true if successful
     */
    public function teleportPlayerToLocation($title, $playerName) {
        if (!array_key_exists($title, $this->locations)) {
            return false;
        }

        $player = SJTTournamentTools::getInstance()->getServer()->getPlayer($playerName);

        if (!$player) {
            return false;
        }

        $location = $this->locations[$title];
        $player->teleport(new Vector3($location['x'], $location['y'], $location['z']));

        return true;
    }

    /**
     * Teleport a set of players to a grid pattern around a location
     *
     * @param string $title The title of the location
     * @param array $playerNames The array of player names
     * @param int $separation The distance between each player
     * @param int $cols The number of columns of players
     * @return boolean true if successful
     */
    public function teleportPlayersToGrid($title, $playerNames, $separation, $cols) {
        if (!array_key_exists($title, $this->locations)) {
            return false;
        }

		$location = $this->locations[$title];
        $plugin = SJTTournamentTools::getInstance();

        $count = count($playerNames);
		for ($i = 0; $i < $count; $i++) {
            $player = $plugin->getServer()->getPlayer($playerNames[$i]);

            if (!$player) {
                continue;
            }

			$x = $location['x'] + 2 + ((int)($i / $cols) * $separation);
			$y = $location['y'] + 1;
			$z = $location['z'] - 1 + (($i % $cols) * $separation);

            $player->teleport(new Vector3($x, $y, $z));
		}

        return true;
	}

    /**
     * Teleport a set of players to a line at a location
     *
     * @param string $title The title of the location
     * @param array $playerNames The array of player names
     * @param type $yaw The yaw (direction) to point the player
     * @return boolean true if successful
     */
    public function teleportPlayersToLine($title, $playerNames, $yaw) {
        if (!array_key_exists($title, $this->locations)) {
            return false;
        }

		$location = $this->locations[$title];
        $plugin = SJTTournamentTools::getInstance();

		$count = count($playerNames);
		for ($i = 0; $i < $count; $i++) {
            $player = $plugin->getServer()->getPlayer($playerNames[$i]);

            if (!$player) {
                continue;
            }

			if ($i % 2) {
				$x = $location["x"] - (2 * ($i + 1) / 2);
			} else {
				$x = $location["x"] + 2 * ($i / 2);
			}
            $y = $location['y'];
			$z = $location['z'] - 3;

            $player->setRotation($yaw, 0);
            $player->teleport(new Vector3($x, $y, $z));
		}

       return true;
	}

    /**
     * Teleport a set of players to a circle centred around a location.  The radius of the circle varies depending on
     * the number of players.
     *
     * @param string $title The title of the location
     * @param array $playerNames The array of player names
     * @return boolean true if successful
     */
    public function teleportPlayersToCircle($title, $playerNames) {
        if (!array_key_exists($title, $this->locations)) {
            return false;
        }

		$location = $this->locations[$title];
        $plugin = SJTTournamentTools::getInstance();

        $count = count($playerNames);
        $radius = 2 + (int)($count / 4);
		$angle = 360 / $count;

		for ($i = 0; $i < $count; $i++) {
            $player = $plugin->getServer()->getPlayer($playerNames[$i]);

            if (!$player) {
                continue;
            }

			$x = $location['x'] + ($radius * sin(deg2rad($angle * $i)));
            $y = $location['y'];
			$z = $location['z'] + ($radius * cos(deg2rad($angle * $i)));

            $player->setRotation(180 - ($angle * $i), 0);
			$player->teleport(new Vector3($x, $y, $z));
		}

        return true;
	}
}
