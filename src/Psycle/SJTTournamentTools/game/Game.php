<?php

namespace Psycle\SJTTournamentTools\Game;

/**
 * The superclass of all types of game
 *
 * @author austin
 */
abstract class Game {
    /** @var string The message shown to the user when the game starts */
    protected $message = "";

    /** @var int The duration of the game in minutes */
    protected $duration = 10;

    /**
     * Constructor
     *
     * @param array $config The configuration array for the game (from the config file)
     */
    public function __construct($config) {
        $this->message = $config['message'];
        $this->duration = $config['duration'];
    }

    public function start() {

    }

    public function stop() {

    }

    abstract function getStatus();
}
