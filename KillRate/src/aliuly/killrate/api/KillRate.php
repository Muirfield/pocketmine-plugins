<?php
namespace aliuly\killrate\api;

use aliuly\killrate\Main as KillRatePlugin;
use pocketmine\Player;

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
   * @param Player|str $player - Player that is scoring
   * @param str $col - Type of data to update
   * @param int $incr - Amount to increment
   * @return int
   */
	public function updateScore($player,$col = "points",$incr = 1) {
    if ($player instanceof Player) $player = $player->getName();
    return $this->plugin->updateDb($player, $col, $incr)
  }
  /**
   * Returns a player's specific score.
   * @param Player|str $player - Player that is scoring
   * @param str $col - Type of data to update
   * @return int
   */
	public function getScore($player,$col = "points") {
    if ($player instanceof Player) $player = $player->getName();
    return $this->plugin->getScore($player)
	}
  /**
   * Set the score value
   * @param Player|str $player - Player that is scoring
   * @param str $col - Type of data to update
   * @param int $score - new score
   * @return int
   */
	public function setScore($player,$col = "points",$score) {
    if ($player instanceof Player) $player = $player->getName();
    $old_score = $this->plugin->getScore($player,$col);
    return $this->plugin->updateDb($player, $col, $score - $old_score);
  }
  /**
   * Returns a player's specific score.
   * @param Player|str $player - Player that is scoring
   * @param str $col - Type of data to update
   * @return int
   */
	public function delScore($pl, $type = null) {
		$this->dbm->delScore($pl->getName(), $type);
	}


}
