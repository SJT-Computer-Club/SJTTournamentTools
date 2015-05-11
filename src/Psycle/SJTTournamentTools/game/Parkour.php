<?php

namespace Psycle\SJTTournamentTools\Game;

use pocketmine\block\Diamond;
use pocketmine\event\block\BlockBreakEvent;
use Psycle\SJTTournamentTools\SJTTournamentTools;

/**
 * Description of Parkour
 *
 * @author austin
 */
class Parkour extends Game {
    /** @var array Player scores */
    private $scores = null;

    /**
     * Constructor.  Teleport all players to the start location.
     *
     * @param array $config The configuration array for the game (from the config file)
     */
    public function __construct($config) {
        parent::__construct($config);

        $this->scores = array();

        $plugin = SJTTournamentTools::getInstance();
        $plugin->getLocationManager()->teleportPlayersToLine('Parkour', $plugin->getPlayers(), 0);
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
     * Handle a BlockBreakEvent
     *
     * @param BlockBreakEvent $event The Event
     */
    public function blockBreakEvent(BlockBreakEvent $event) {
        $player = $event->getPlayer();
        $block = $event->getBlock();

        // Whatever happens, don't allow blocks to be broken during a game
        if (!$player->isOp()) {
            $event->setCancelled();
        }

        if ($block instanceof Diamond) {
            // Handle player scoring
            $this->playerScored($player, $block, 10);
        }
    }

    /**
     * The player has scored
     *
     * @param Player $player The Player
     * @param Block $block The Block the player has scored
     */
    private function playerScored(Player $player, Block $block) {
        $score = 10;
        $playerName = $player->getName;
        $scoreKey = $block->getX() . '-' . $block->getY() . '-' . $block->getZ();

        if (!array_key_exists($playerName, $this->scores)) {
            $this->scores[$playerName] = array();
        }

        if (!array_key_exists($scoreKey, $this->scores[$playerName])) {
            $this->scores[$playerName][$scoreKey] = $score;
            SJTTournamentTools::getInstance()->getServer()->broadcastMessage($playerName . ' scored ' . $score);
        }
    }

    /**
     * Display all scores for this game
     */
    public function displayScores() {
        SJTTournamentTools::getInstance()->getServer()->broadcastMessage('Scores for recent game of Parkour are...');

        foreach ($this->scores as $playerName => $playerScores) {
			$score = 0;
			foreach ($playerScores as $location => $value) {
				$score += $value;
			}

            SJTTournamentTools::getInstance()->getServer()->broadcastMessage($playerName . ' scored ' . $score);
		}
    }
}
