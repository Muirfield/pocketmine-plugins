<?php
namespace aliuly\grabbag\api;

use aliuly\grabbag\Main as GrabBagPlugin;
use pocketmine\Player;

/**
 * GrabBag API
 */
class GrabBag {
  protected $plugin;
  /**
   * @param GrabBagPlugin $owner - plugin that owns this session
   */
  public function __construct(GrabBagPlugin $owner) {
    $this->plugin = $owner;
  }
  /**
   * Check if module is available...
   * @param str $module - module name
   * @return mixed|null
   */
  public function getModule($module) {
    return $this->plugin->getModule($module);
  }
  /**
   * Checks if hard or soft freezing
   * @return bool
   */
  public function isHardFreeze() {
    return $this->getModule("freeze-thaw")->isHardFreeze();
  }
  /**
   * Sets hard or soft freezing
   * @param bool $hard - if true (default) hard freeze is in effect.
   */
  public function setHardFreeze($hard = true) {
    $this->getModule("freeze-thaw")->setHardFreeze($hard);
  }
  /**
   * Freeze given player
   * @param Player $player - player to freeze
   * @param bool $freeze - if true (default) freeze, if false, thaw.
   */
  public function freeze(Player $player, $freeze = true) {
    $this->getModule("freeze-thaw")->freeze($player,$freeze);
  }
  /**
   * Return a list of frozen players
   * @return str[]
   */
  public function getFrosties() {
    return $this->getModule("freeze-thaw")->getFrosties();
  }
}
