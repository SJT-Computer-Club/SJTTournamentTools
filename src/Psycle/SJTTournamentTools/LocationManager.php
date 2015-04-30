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

    public function teleportToLocation($player, $title) {
        if (array_key_exists($title, $this->locations)) {
            $location = $this->locations[$title];
            $player->teleport(new Vector3($location['x'], $location['y'], $location['z']));
            return true;
        } else {
            return false;
        }
    }
}
