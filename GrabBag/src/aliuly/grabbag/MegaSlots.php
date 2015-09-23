<?php
//= module:mega-slots
//: Merges the slot count of multiple servers
//:
//: This module requres either the **motd-task** or **query-task**
//: to be enabled.
namespace aliuly\grabbag;

use aliuly\common\mc;

use pocketmine\event\Listener;

use aliuly\grabbag\api\GbRmQueryEvent;
use aliuly\grabbag\api\GbUpdateQueryEvent;
use aliuly\common\PluginCallbackTask;
use pocketmine\event\server\QueryRegenerateEvent;

class MegaSlots implements Listener {
	public function __construct($owner) {
    $this->owner = $owner;
    if ($this->owner->getModule("motd-task") == null && $this->owner->getModule("query-task") == null) {
      $this->owner->getLogger()->warning(mc::_("Please enable either"));
      $this->owner->getLogger()->warning(mc::_("motd-task or query-task"));
      $this->owner->getLogger()->warning(mc::_("for merge-slots to work"));
      return;
    }
    $this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
  public function onServerQuery(QueryRegenerateEvent $ev) {
    $players = count($this->owner->getServer()->getOnlinePlayers());
    $maxplayers = $this->owner->getServer()->getMaxPlayers();

    $lst = $this->owner->getModule("ServerList");
    foreach ($lst->getIds() as $i) {
			if ($lst->getServerAttr($i,"no-merge-slots",false)) continue;
      if (!$lst->getServerAttr($i,"no-motd-task",false)) {
        $cc = $lst->getQueryData($i,"motd");
        if ($cc !== null) {
					$players += $cc["players"];
					$maxplayers += $cc["max-players"];
        }
      }
      if (!$lst->getServerAttr($i,"no-query-task",false)) {
        $cc = $lst->getQueryData($i,"query.info");
        if ($cc !== null) {
					$players += $cc["Players"];
					$maxplayers += $cc["MaxPlayers"];
        }
      }
    }

    $ev->setPlayerCount($players);
    $ev->setMaxPlayerCount($maxplayers);

  }
  public function onRmQuery(GbRmQueryEvent $ev) {
    $this->scheduleQueryRegen();
  }
  public function onUpdQuery(GbUpdateQueryEvent $ev) {
    $this->scheduleQueryRegen();
  }
  private function scheduleQueryRegen() {
    $this->owner->getServer()->getScheduler()->scheduleDelayedTask(
      new PluginCallbackTask($this->owner, [$this,"delayedQueryRegen"],[]),
      5
    );
  }
  public function delayedQueryRegen() {
    $this->onServerQuery($q = $this->owner->getServer()->getQueryInformation());
  }
}
