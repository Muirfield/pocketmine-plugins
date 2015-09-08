<?php
namespace aliuly\killrate\api;

use aliuly\killrate\Main as KillRatePlugin;
use pocketmine\event\Cancellable;
use pocketmine\Player;
use aliuly\killrate\api\KillRateEvent;

/**
 * Triggered when the player is scoring points
 */
class KillRateScoreEvent extends KillRateEvent implements Cancellable {
  public static $handlerList = null;
  /** @var Player */
  private $player;
  private $type;
  private $points;
  private $money;
  private $incr;
  /**
   * @param KillRatePlugin $plugin - plugin owner
   * @param Player $Player - player making the score
   * @param str $col - type of victim
   * @param int $points - points being awarded
   * @param int $money - money being awarded
   * @param int $incr - increment values
   */
   public function __construct(KillRatePlugin $plugin, Player $player, $col, $points = 0, $money = 0, $incr = 1) {
     parent::__construct($plugin);
     $this->player = $player;
     $this->type = $col;
     $this->points = $points;
     $this->money = $money;
     $this->incr = $incr;
   }
   /**
    * @return Player
    */
    public function getPlayer() {
      return $this->player;
    }
    public function getType() {
      return $this->type;
    }
    public function setType($col) {
      $this->type = $col;
    }
    public function getPoints() {
      return $this->points;
    }
    public function setPoints($points) {
      $this->points = $points;
    }
    public function getMoney() {
      return $this->money;
    }
    public function setMoney($money) {
      $this->money = $money;
    }
    public function getIncr() {
      return $this->incr;
    }
    public function setIncr($incr) {
      $this->incr = $incr;
    }
}
