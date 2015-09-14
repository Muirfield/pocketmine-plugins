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
   * Return ranked scores
   *
   * Returns an array with each element having a ["player_name"] and
   * all the values from `getScores`.
   *
   * @param int $limit - Max number of players to rank
   * @param bool $online - if true limit rankings to on-line players
   * @param str $col - Type of data to sort
   * @return array
   */
  public function getRankedScores($limit=10,$online=false,$col = "points") {
    $tab =  $this->plugin->getRankings($limit,$online,$col);
    $res = [];
    foreach ($tab as $n) {
      $pn = $n["player"];
      $scores = $this->plugin->getScores($pn);
      if ($scores == null) continue;
      $row = [ "player_name" => $pn ];
      foreach ($scores as $dt) {
        $row[$dt["type"]] = $dt["count"];
      }
      if (isset($row["deaths"]) && isset($row["player"]) && $row["deaths"] != 0) {
        $row["kdratio"] = round((float)$row["player"]/$row["deaths"],2);
      }
      $res[] = $row;
    }
    return $res;
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
   * Update Database values.
   * @param IPlayer|str $player - Player that is scoring
   * @param int $val - Value to set to
   * @param str $col - Type of data to update
   */
	public function setScore($player,$val, $col = "points") {
    if ($player instanceof IPlayer) $player = $player->getName();
    return $this->plugin->setScore($player, $val, $col);
  }
  /**
   * Returns a player's specific score.
   * @param IPlayer|str $player - Player that is scoring
   * @param str $col - Type of data to update
   * @return int|float
   */
	public function getScore($player,$col = "points") {
    if ($col == "kdratio") return $this->getKDRatio($player);
    if ($player instanceof IPlayer) $player = $player->getName();
    return $this->plugin->getScoreV2($player);
	}
  /**
   * Get KillDeath Ratio
   * @param IPlayer|str $player - Player that is scoring
   * @return int|null
   */
  public function getKDRatio($player) {
    if ($player instanceof IPlayer) $player = $player->getName();
    $d = $this->plugin->getScoreV2($player,"deaths");
    $k = (float)$this->plugin->getScoreV2($player,"player");
    if ($d == 0) return null;
    return round($k/$d,2);
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
