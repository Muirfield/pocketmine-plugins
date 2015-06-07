<?php
namespace aliuly\spawnmgr;

use pocketmine\item\Item;
use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use SimpleAuth\event\PlayerRegisterEvent;

class SimpleAuthMgr implements Listener {
	public $owner;
	protected $nest_egg;

	public function __construct(Plugin $plugin,$ne) {
		$this->owner = $plugin;
		$this->nest_egg = $ne;
	}
	public function onRegister(PlayerRegisterEvent $ev) {
		$pl = $ev->getPlayer();
		if (!$pl->hasPermission("spawnmgr.receive.nestegg")) return;
		foreach ($this->nest_egg as $i) {
			$r = explode(",",$i);
			if (count($r) != 2) continue;
			$item = Item::fromString($r[0]);
			$item->setCount(intval($r[1]));
			$pl->getInventory()->addItem($item);
		}
	}
}
