<?php

namespace Psycle\SJTTournamentTools\Game;

use Psycle\SJTTournamentTools\SJTTournamentTools;

/**
 * Description of Parkour
 *
 * @author austin
 */
class TreasureHunt extends Game {
    /**
     * Constructor
     *
     * @param array $config The configuration array for the game (from the config file)
     */
    public function __construct($config) {
        parent::__construct($config);

        $plugin = SJTTournamentTools::getInstance();
        $plugin->getLocationManager()->teleportPlayersToCircle('TreasureHunt', $plugin->getPlayers());
    }

    public function start() {
        parent::start();
    }

    public function stop() {
        parent::stop();
    }

    public function getStatus() {

    }
}
