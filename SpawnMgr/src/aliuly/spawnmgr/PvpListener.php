<?php
namespace aliuly\spawnmgr;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase as Plugin;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\Player;

class PvpListener implements Listener {
	protected $pvp;
	public function __construct(Plugin $plugin) {
		$this->owner = $plugin;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	public function onPvP(EntityDamageEvent $ev) {
		if ($ev->isCancelled()) return;
		if(!($ev instanceof EntityDamageByEntityEvent)) return;
		$et = $ev->getEntity();
		if(!($et instanceof Player)) return;
		$sp = $et->getLevel()->getSpawnLocation();
		$dist = $sp->distance($et);
		if ($dist > $this->owner->getServer()->getSpawnRadius()) return;
		$ev->setCancelled();
	}

}
