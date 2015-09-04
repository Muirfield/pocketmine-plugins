<?php
namespace aliuly\grabbag\api;

use aliuly\grabbag\Main as GrabBagPlugin;
use aliuly\grabbag\api\GrabBagEvent;
use pocketmine\event\Cancellable;

class GbAddServerEvent extends GrabBagEvent implements Cancellable {
  public static $handlerList = null;
  /**
   * @param GrabBagPlugin $plugin - plugin owner
   */
   public function __construct(GrabBagPlugin $plugin) {
     parent::__construct($plugin);
   }
}
