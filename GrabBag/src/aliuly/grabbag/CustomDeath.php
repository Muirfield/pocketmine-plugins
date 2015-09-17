<?php
//= module:custom-death
//: Customize what happens when a player dies
//:
namespace aliuly\grabbag;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;

use aliuly\common\PermUtils;
use aliuly\common\mc;

class CustomDeath implements Listener {
	public $owner;
  protected $keepinv;

	static public function defaults() {
		//= cfg:custom-death
		return [
			"# inv" => "default, keep, nodrops, perms",
			"inv" => "default",
		];
	}

	public function __construct(Plugin $plugin,$cfg) {
		$this->owner = $plugin;
		PermUtils::add($this->owner, "gb.cdeath", "players with this permission benefit from keepiinv", "true");

		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);

		$this->keepinv = $cfg["inv"];
		if ($this->keepinv == "perms") {
			PermUtils::add($this->owner, "gb.cdeath.default", "Player dies according to PocketMine defaults", "false");
			PermUtils::add($this->owner, "gb.cdeath.keep", "Player keeps inventory on death", "false");
			PermUtils::add($this->owner, "gb.cdeath.nodrops", "Player does not drop items on death", "false");
		}
	}
	public function onPlayerDeath(PlayerDeathEvent $e) {
		$pl = $e->getPlayer();
    if (!$pl->hasPermission("gb.cdeath")) return;
    $keepinv = $this->keepinv;
    if($keepinv == "perms") {
      foreach(["keep","nodrops","default"] as $m) {
        if ($pl->hasPermission("gb.keepinv.".$m)) break;
      }
    }
    switch ($keepinv) {
      case "keep":
        $e->setKeepInventory(true);
        $e->setDrops([]);
        break;
      case "nodrops":
        $e->setKeepInventory(false);
        $e->setDrops([]);
        break;
    }
  }
}
