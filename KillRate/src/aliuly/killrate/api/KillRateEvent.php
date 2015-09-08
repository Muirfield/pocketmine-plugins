<?php
namespace aliuly\killrate\api;

use aliuly\killrate\Main as KillRatePlugin;
use pocketmine\event\plugin\PluginEvent;

/**
 * Basic KillRate event class
 */
abstract class KillRateEvent extends PluginEvent {
  /**
   * @param KillRatePlugin $PluginEvent
   */
   public function __construct(KillRatePlugin $plugin) {
     parent::__construct($plugin);
   }
}
