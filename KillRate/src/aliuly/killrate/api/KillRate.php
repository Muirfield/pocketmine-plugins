<?php
namespace aliuly\killrate\api;

use aliuly\killrate\Main as KillRatePlugin;
use pocketmine\IPlayer;

/**
 * KillRate API
 */
class KillRate {
  protected $plugin;
  /**
   * @param KillRatePlugin $owner - plugin that owns this session
   */
  public function __construct(KillRatePlugin $owner) {
    $this->plugin = $owner;
  }
  /**
   * Show rankings
   *
   * Returns an array with each element having a ["player"] and ["count"].
   *
   * @param int $limit - Max number of players to rank
   * @param bool $online - if true limit rankings to on-line players
   * @param str $col - Type of data to return
   * @return array
   */
  public function getRankings($limit=10,$online=false,$col = "points") {
    return $this->plugin->getRankings($limit,$online,$col);
  }
  /**
   * Update Database values.  Returns the new updated value.
   * @param IPlayer|str $player - Player that is scoring
   * @param str $col - Type of data to update
   * @param int $incr - Amount to increment
   * @return int
   */
	public function updateScore($player,$col = "points",$incr = 1) {
    if ($player instanceof IPlayer) $player = $player->getName();
    return $this->plugin->updateDb($player, $col, $incr);
  }
  /**
   * Returns a player's specific score.
   * @param IPlayer|str $player - Player that is scoring
   * @param str $col - Type of data to update
   * @return int
   */
	public function getScore($player,$col = "points") {
    if ($player instanceof IPlayer) $player = $player->getName();
    return $this->plugin->getScoreV2($player);
	}
  /**
   * Set the score value
   * @param IPlayer|str $player - Player that is scoring
   * @param int $score - new score
   * @param str $col - Type of data to update
   * @return int
   */
	public function setScore($player,$score, $col = "points") {
    if ($player instanceof IPlayer) $player = $player->getName();
    $old_score = $this->plugin->getScoreV2($player,$col);
    return $this->plugin->updateDb($player, $col, $score - $old_score);
  }
  /**
   * Returns a player's specific score.
   * @param IPlayer|str $player - Player that is scoring
   * @param str $col - Type of data to update
   * @return int
   */
	public function delScore($pl, $type = null) {
    if ($player instanceof IPlayer) $player = $player->getName();
		$this->plugin->delScore($player, $type);
	}
}
