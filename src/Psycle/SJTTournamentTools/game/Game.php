<?php

namespace Psycle\SJTTournamentTools\Game;

/**
 * The superclass of all types of game
 *
 * @author austin
 */
class Game {
    /**
     * The message shown to the user when the game starts
     * @var type string
     */
    protected $message = "";

    /**
     * The duration of the game in minutes
     * @var type int
     */
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
}
