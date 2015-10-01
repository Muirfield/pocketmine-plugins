<?php

namespace aliuly\common;
use aliuly\common\Session;
use aliuly\common\MPMU;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\plugin\PluginBase;

/**
 * Shielded Player sessions
 *
 * NOTE, if GrabBag is available, it will use the GrabBag shield
 * implementation.  This gives you a command line interface and also
 * reduces the number of listeners in use.
 */
class ShieldSession extends Session {
  protected $api;
  /**
   * @param PluginBase $owner - plugin that owns this session
   * @param bool $hard - hard freeze option
   */
  public function __construct(PluginBase $owner, $hard = true) {
    $bag = $owner->getServer()->getPluginManager()->getPlugin("GrabBag");
    if ($bag && $bag->isEnabled() && MPMU::apiCheck($bag->getDescription()->getVersion(),"2.3") && $bag->api->getFeature("shield")) {
      $this->api = $bag->api;
      return;
    }
    parent::__construct($owner);
    $this->api = null;
  }
  /**
   * Return player's shield status
   * @param Player $target
   * @return bool
   */
  public function isShielded(Player $target) {
    if ($this->api !== null) return $this->api->isShielded($target);
    return $this->getState("shield",$target,false);
  }
  /**
   * Turn on/off shields
   * @param Player $target
   * @param bool $mode - true is shielded, false is not
   */
  public function setShield(Player $target,$mode) {
    if ($this->api !== null) {
      $this->api->setShield($target,$mode);
      return;
    }
    if ($mode) {
      $this->setState("shield",$target,true);
    } else {
      $this->unsetState("shield",$target);
    }
  }

  public function onDamage(EntityDamageEvent $ev) {
    if ($ev->isCancelled()) return;
    if(!($ev instanceof EntityDamageByEntityEvent)) return;
    if (!($ev->getEntity() instanceof Player)) return;
    if (!$this->getState("shield",$ev->getEntity(),false)) return;
    $ev->setCancelled();
  }
}
