<?php
//= module:custom-death
//: Customize what happens when a player dies
//:
//: Currently this module only supports the _KeepInv_ feature.
//: This feature lets you select what happens with a player's inventory
//: when they get killed.
//:
//: - default : This is the PocketMine-MP default, which the player
//:   loses their inventory and it is drop as pickable items.
//: - keep : The player gets to keep their inventory and nothing gets
//:   dropped.
//: - nodrops : The player loses their inventory but no items are dropped.
//:   This is useful to reduce the amount of Item Entities which in heavy
//:   used servers may cause lag.
//: - perms: Player permissions are checked on what to do.  Players must
//:   have one permission between these:
//:   - gb.cdeath.default
//:   - gb.cdeath.keep
//:   - gb.cdeath.nodrops
//:
namespace aliuly\grabbag;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\Player;

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
		$pl = $e->getEntity();
		if (!($pl instanceof Player)) return;
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
