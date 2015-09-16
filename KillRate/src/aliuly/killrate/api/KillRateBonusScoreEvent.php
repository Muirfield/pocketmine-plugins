<?php
namespace aliuly\killrate\api;

use aliuly\killrate\Main as KillRatePlugin;
use pocketmine\event\Cancellable;
use pocketmine\Player;
use aliuly\killrate\api\KillRateEvent;

/**
 * Triggered when the player is scoring points
 */
class KillRateBonusScoreEvent extends KillRateEvent implements Cancellable {
  public static $handlerList = null;
  /** @var Player */
  private $player;
  private $money;
  /**
   * @param KillRatePlugin $plugin - plugin owner
   * @param Player $Player - player making the score
   * @param Player $victim - victim of last score
   * @param int $money - money being awarded
   */
  public function __construct(KillRatePlugin $plugin, Player $player, $money = 0) {
    parent::__construct($plugin);
    $this->player = $player;
    $this->money = $money;
  }
  /**
   * @return Player
   */
  public function getPlayer() {
    return $this->player;
  }
  public function getMoney() {
    return $this->money;
  }
  public function setMoney($money) {
    $this->money = $money;
  }
}
