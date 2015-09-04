<?php
namespace aliuly\grabbag\api;

use aliuly\grabbag\Main as GrabBagPlugin;
use pocketmine\event\plugin\PluginEvent;

abstract class GrabBagEvent extends PluginEvent {
  /**
   * @param GrabBagPlugin $plugin
   */
   public function __construct(GrabBagPlugin $plugin) {
     parent::__construct($plugin);
   }
}
