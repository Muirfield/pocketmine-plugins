<?php
namespace aliuly\spawnmgr;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase as Plugin;

use pocketmine\event\entity\EntityExplodeEvent;

class TntListener implements Listener {
	protected $pvp;
	public function __construct(Plugin $plugin) {
		$this->owner = $plugin;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	public function onExplode(EntityExplodeEvent $ev){
		if ($ev->isCancelled()) return;
		$et = $ev->getEntity();
		$sp = $et->getLevel()->getSpawnLocation();
		$dist = $sp->distance($et);
		if ($dist > $this->owner->getServer()->getSpawnRadius()) return;
		$ev->setCancelled();
	}
}
