<?php
namespace aliuly\worldprotect;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;


use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\Player;


class WpPvpMgr implements Listener {
	public $owner;
	public function __construct(Plugin $plugin) {
		$this->owner = $plugin;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	public function onPvP(EntityDamageEvent $ev) {
		if ($ev->isCancelled()) return;
		if(!($ev instanceof EntityDamageByEntityEvent)) return;
		if (!($ev->getEntity() instanceof Player && $ev->getDamager() instanceof Player)) return;
		if ($this->owner->checkPvP($ev->getEntity()->getLevel()->getName())) return;
		$this->owner->msg($ev->getDamager(),"You are not allowed to do that here");
		$ev->setCancelled();
	}
}
