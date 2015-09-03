<?php
namespace aliuly\grabbag\api;

use aliuly\grabbag\Main as GrabBagPlugin;
use pocketmine\Player;
use aliuly\grabbag\common\mc;

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
   * This will throw an exception if the module is not available
   * @param str $module - module name
   * @return mixed|null
   */
  public function getModule($module) {
    $vp = $this->plugin->getModule($module);
    if ($vp === null) throw new \RuntimeException("Missing module: " . $module);
    return $vp;
  }
  /**
   * Check if feature is supported...
   * @param str $feature - module name
   * @return bool
   */
   public function getFeature($feature) {
     if (!in_array($feature,["freeze-thaw", "invisible"])) return false;
     if ($this->plugin->getModule($feature) === null) return false;
     return true;
   }
   /**
    * Currently un-implemented
    */
   public function getVars() {
     return null;
   }
   /**
    * Currently un-implemented
    */
   public function getInterp() {
     return null;
   }

  //////////////////////////////////////////////////////////////
  // CmdFreeze
  //////////////////////////////////////////////////////////////
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
  //////////////////////////////////////////////////////////////
  // CmdInvisible
  //////////////////////////////////////////////////////////////
  /**
   * Make player invisible
   * @param Player $player - player to change
   * @param bool $invis - if true (default) invisible, if false, visible.
   */
  public function invisible(Player $player, $invis) {
    if ($invis) {
      if (!$this->getModule("invisible")->isInvisible($player))
        $this->getModule("invisible")->activate($player);
    } else {
      if ($this->getModule("invisible")->isInvisible($player))
        $this->getModule("invisible")->deactivate($player);
    }
  }
  /**
   * Check if player is invisible...
   * @param Player $player - player to check
   */
  public function isInvisible(Player $player) {
    return $this->getModule("invisible")->isInvisible($player);
  }
  //////////////////////////////////////////////////////////////
  // CmdAfterAt
  //////////////////////////////////////////////////////////////
  /**
   * Schedule a command to be run
   * @param int $secs - execute after this number of seconds
   * @param str $cmdline - command line to execute
   */
  public function after($cmdline,$secs) {
    $this->getModule("after-at")->schedule($secs,$cmdline);
  }
}
