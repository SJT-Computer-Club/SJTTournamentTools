<?php

namespace Psycle\SJTTournamentTools\Game;

use Psycle\SJTTournamentTools\SJTTournamentTools;

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

        SJTTournamentTools::getInstance()->getServer()->broadcastMessage($this->message);
    }

    /**
     * Start the game
     */
    public function start() {
        $this->startTime = time();
        $this->running = true;

        SJTTournamentTools::getInstance()->getServer()->broadcastMessage('Go! Go! Go!');
        SJTTournamentTools::getInstance()->getServer()->broadcastMessage($this->duration . ' minutes and counting!');
    }

    /**
     * Stop the game
     */
    public function stop() {
        $this->running = false;

        SJTTournamentTools::getInstance()->getServer()->broadcastMessage('It\'s all over!');
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
            SJTTournamentTools::getInstance()->getGameManager()->stopGame();
            return false;
        } elseif ($secondsToGo % 60 == 0) {
            SJTTournamentTools::getInstance()->getServer()->broadcastMessage((int)($secondsToGo / 60) . ' minute' . ((int)($secondsToGo / 60) == 1 ? '' : 's') . ' to go');
        } elseif ($secondsToGo == 30) {
            SJTTournamentTools::getInstance()->getServer()->broadcastMessage('==>> ' . $secondsToGo . ' seconds!');
        } elseif ($secondsToGo <= 10) {
            SJTTournamentTools::getInstance()->getServer()->broadcastMessage('==>> ' . $secondsToGo . '!');
        }

        return true;
    }

    /**
     * Check whether the game is running
     *
     * @return boolean true if the game is running
     */
    public function isRunning() {
        return $this->running;
    }


    /**
     * Set the game mode for the players
     *
     * @param type $gameMode The game mode: Player::SURVIVAL | Player::CREATIVE || Player::ADVENTURE || Player::SPECTATOR
     */
    protected function setGameMode($gameMode) {
        $plugin = SJTTournamentTools::getInstance();

		foreach ($plugin->getPlayers() as $playerName) {
            $player = $plugin->getServer()->getPlayer($playerName);

            if (!$player) {
                continue;
            }

            $player->setGameMode($gameMode);
		}
    }

    abstract function getStatus();

    abstract function displayScores();
}
