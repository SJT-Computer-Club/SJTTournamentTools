<?php

namespace Psycle\SJTTournamentTools\Game;

use \Psycle\SJTTournamentTools\SJTTournamentTools;

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

    /** @var bool true if the game is running*/
    protected $running = false;

    /** @var int The start time of the game, in seconds since epoch */
    protected $startTime = 0;

    /**
     * Constructor
     *
     * @param array $config The configuration array for the game (from the config file)
     */
    public function __construct($config) {
        $this->message = $config['message'];
        $this->duration = $config['duration'];
    }

    /**
     * Start the game
     */
    public function start() {
        $this->startTime = time();
        $this->running = true;
    }

    /**
     * Stop the game
     */
    public function stop() {
        $this->running = false;
    }

    /**
     * Tick the game, called every second
     *
     * @return boolean true if game is continuing, false if we have ended
     */
    public function tick() {
        if (!$this->running) {
            return false;
        }

        $secondsToGo = $this->startTime + $this->duration * 60 - time();

        if ($secondsToGo <= 0) {
            $this->stop();
            return false;
        } elseif ($secondsToGo % 60 == 0) {
            SJTTournamentTools::getInstance()->getServer()->broadcastMessage((int)($secondsToGo / 60) . ' minutes to go');
        } elseif ($secondsToGo == 30) {
            SJTTournamentTools::getInstance()->getServer()->broadcastMessage('==>> ' . $secondsToGo . ' !');
        } elseif ($secondsToGo <= 10) {
            SJTTournamentTools::getInstance()->getServer()->broadcastMessage('==>> ' . $secondsToGo . ' !');
        }

        return true;
    }

    abstract function getStatus();
}
