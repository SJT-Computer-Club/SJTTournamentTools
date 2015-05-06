<?php

namespace Psycle\SJTTournamentTools;

use Psycle\SJTTournamentTools\Game\Build;
use Psycle\SJTTournamentTools\Game\Parkour;
use Psycle\SJTTournamentTools\Game\TreasureHunt;

/**
 * Manages Games
 *
 * @author austin
 */
class GameManager {

    /** Types of game */
    const GAME_TYPE_BUILD = 'Build',
          GAME_TYPE_PARKOUR = 'Parkour',
          GAME_TYPE_TREASUREHUNT = 'TreasureHunt';

    /** @var Psycle\SJTTournamentTools\Game The currently active game, null if no game active. */
    private $currentGame = null;

    /** @var array Configuration array */
    private $config = null;

    /**
     * Constructor
     *
     * @param array $config Array containing games from config file
     */
    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * Create a new game of a given type
     *
     * @param int $gameType GAME_TYPE_BUILD | GAME_TYPE_PARKOUR | GAME_TYPE_TREASUREHUNT
     * @return bool true if successful
     */
    public function setupGame($gameType) {
        if ($this->currentGame !== null) {
            return false;
        }

        switch ($gameType) {
            case self::GAME_TYPE_BUILD:
                $this->currentGame = new Build($this->config['Build']);
                break;
            case self::GAME_TYPE_PARKOUR:
                $this->currentGame = new Parkour($this->config['Parkour']);
                break;
            case self::GAME_TYPE_TREASUREHUNT:
                $this->currentGame = new TreasureHunt($this->config['TreasureHunt']);
                break;
        }

        return true;
    }

    /**
     * Returns the current game type, or null if none.
     *
     * @return string null | GAME_TYPE_BUILD | GAME_TYPE_PARKOUR | GAME_TYPE_TREASUREHUNT
     */
    public function getCurrentGameType() {
        if ($this->currentGame == null) {
            return null;
        }

        $reflect = new \ReflectionClass($this->currentGame);
        return $reflect->getShortName();
    }

    /**
     * Start the game
     */
    public function startGame() {
        if ($this->currentGame !== null) {
            $this->currentGame->start();
        }
    }

    /**
     * Tick the current game. Called every second.
     */
    public function tick() {
        if ($this->currentGame !== null) {
            $this->currentGame->tick();
        }
    }
}
