<?php
namespace aliuly\killrate\api;

use aliuly\killrate\Main as KillRatePlugin;
use pocketmine\event\Cancellable;
use pocketmine\Player;
use aliuly\killrate\api\KillRateEvent;

class KillRateResetEvent extends KillRateEvent implements Cancellable {
  public static $handlerList = null;
  /** @var Player */
  private $player;
  /**
   * @param KillRatePlugin $plugin - plugin owner
   * @param Player $Player - player making the score
   */
   public function __construct(KillRatePlugin $plugin, Player $player) {
     parent::__construct($plugin);
     $this->player = $player;
   }
   /**
    * @return Player
    */
    public function getPlayer() {
      return $this->player;
    }
}
