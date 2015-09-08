<?php

namespace aliuly\common;
use aliuly\common\Session;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;

/**
 * Invisible Player sessions
 *
 * NOTE, if GrabBag is available, it will use the GrabBag invisible
 * implementation.  This gives you a command line interface and also
 * reduces the number of listeners in use.
 */
class InvisibleSession extends Session {
  protected $api;
  /**
   * @param PluginBase $owner - plugin that owns this session
   */
  public function __construct(PluginBase $owner) {
    $bag = $owner->getServer()->getPluginManager()->getPlugin("GrabBag");
    if ($bag && $bag->isEnabled() && MPMU::apiCheck($bag->getDescription()->getVersion(),"2.3") && $bag->api->getFeature("invisible")) {
      $this->api = $bag->api;
      return;
    }
    parent::__construct($owner);
    $this->api = null;
  }
  /**
   * Make player invisible
   * @param Player $player - player to change
   * @param bool $invis - if true (default) invisible, if false, visible.
   */
  public function invisible(Player $player, $invis) {
    if ($this->api !== null) {
      $this->api->invisible($player,$invis);
      return;
    }
    if ($invis) {
      if ($this->getState("invis",$player,false)) return;
      $this->setState("invis",$player,true);
      foreach($this->owner->getServer()->getOnlinePlayers() as $online){
        $online->hidePlayer($player);
      }
    } else {
      if (!$this->getState("invis",$player,false)) return;
      $this->unsetState("invis",$player);
      foreach($this->owner->getServer()->getOnlinePlayers() as $online){
        $online->showPlayer($player);
      }
    }
  }
  /**
   * Check if player is invisible...
   * @param Player $player - player to check
   */
  public function isInvisible(Player $player) {
    if ($this->api !== null) return $this->api->isInvisible($player);
    return $this->getState("invis",$player,false);
  }
  /**
   * Make sure that players are invisible to recent joiners...
   * --- this will conflict with SimpleAuthHelper's hide unauth players
   */
  public function onPlayerJoin(PlayerJoinEvent $e) {
		$pl = $e->getPlayer();
		foreach($this->owner->getServer()->getOnlinePlayers() as $online){
			if ($this->getState("invis",$online,false)) {
				$pl->hidePlayer($online);
			}
		}
	}
}
