<?php

namespace aliuly\common;
use aliuly\common\Session;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use aliuly\common\MPMU;

/**
 * Frozen Player sessions
 *
 * NOTE, if GrabBag is available, it will use the GrabBag freeze-thaw
 * implementation.  This gives you a command line interface and also
 * reduces the number of listeners in use.
 */
class FreezeSession extends Session {
  protected $hard;
  protected $api;
  /**
   * @param PluginBase $owner - plugin that owns this session
   * @param bool $hard - hard freeze option
   */
  public function __construct(PluginBase $owner, $hard = true) {
    $bag = $owner->getServer()->getPluginManager()->getPlugin("GrabBag");
    if ($bag && $bag->isEnabled() && MPMU::apiCheck($bag->getDescription()->getVersion(),"2.3") && $bag->api->getFeature("freeze-thaw")) {
      $this->api = $bag->api;
      return;
    }
    parent::__construct($owner);	// We do it here so to prevent the registration of listeners
    $this->api = null;
    $this->hard = $hard;
  }
  /**
	 * Handle player move events.
   * @param PlayerMoveEvent $ev - Move event
	 */
  public function onMove(PlayerMoveEvent $ev) {
    //echo __METHOD__.",".__LINE__."\n";//##DEBUG
    if ($ev->isCancelled()) return;
    $p = $ev->getPlayer();
    if (!$this->getState("fz",$p,false)) return;
    if ($this->hard) {
      $ev->setCancelled();
    } else {
      // Lock position but still allow to turn around
      $to = clone $ev->getFrom();
      $to->yaw = $ev->getTo()->yaw;
      $to->pitch = $ev->getTo()->pitch;
      $ev->setTo($to);
    }
  }
  /**
   * Checks if hard or soft freezing
   * @return bool
   */
  public function isHardFreeze() {
    if ($this->api !== null) return $this->api->isHardFreeze();
    return $this->hard;
  }
  /**
   * Sets hard or soft freezing
   * @param bool $hard - if true (default) hard freeze is in effect.
   */
  public function setHardFreeze($hard = true) {
    if ($this->api !== null) {
      $this->api->setHardFreeze($hard);
    } else {
      $this->hard = $hard;
    }
  }
  /**
   * Freeze given player
   * @param Player $player - player to freeze
   * @param bool $freeze - if true (default) freeze, if false, thaw.
   */
  public function freeze(Player $player, $freeze = true) {
    if ($this->api !== null) {
      $this->api->freeze($player,$freeze);
      return;
    }
    if ($freeze) {
      $this->setState("fz",$player,true);
    } else {
      $this->unsetState("fz",$player);
    }
  }
  /**
   * Return a list of frozen players
   * @return str[]
   */
  public function getFrosties() {
    if ($this->api !== null) return $this->api->getFrosties();
    $s = [];
    foreach ($this->state as $n=>$d) {
      if (isset($d["fz"])) $s[] = $n;
    }
    return $s;
  }
}
