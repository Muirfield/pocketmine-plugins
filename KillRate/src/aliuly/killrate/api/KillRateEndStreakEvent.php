<?php
namespace aliuly\killrate\api;

use aliuly\killrate\Main as KillRatePlugin;
use pocketmine\event\Cancellable;
use pocketmine\Player;
use aliuly\killrate\api\KillRateEvent;

/**
 * Triggered when the player is scoring points
 */
class KillRateEndStreakEvent extends KillRateEvent implements Cancellable {
  public static $handlerList = null;
  /** @var Player */
  private $player;
  private $kills;
  /**
   * @param KillRatePlugin $plugin - plugin owner
   * @param Player $Player - player making the score
   * @param int $newstreak
   * @param int $oldstreak
   */
  public function __construct(KillRatePlugin $plugin, Player $player, $streak) {
    parent::__construct($plugin);
    $this->player = $player;
    $this->kills = $streak;
  }
  /**
   * @return Player
   */
  public function getPlayer() {
    return $this->player;
  }
  public function getKills() {
    return $this->kills;
  }
}
