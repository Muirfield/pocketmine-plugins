<?php
namespace aliuly\worldprotect;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;

use pocketmine\Player;
use pocketmine\scheduler\CallbackTask;


class WpMotdMgr implements Listener {
	public $owner;

	public function __construct(Plugin $plugin) {
		$this->owner = $plugin;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	private function showMotd($name,$level,$ticks=10) {
		$this->owner->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this->owner,"showMotd"],[$name,$level]),$ticks);
	}
	public function onJoin(PlayerJoinEvent $ev) {
		$pl = $ev->getPlayer();
		$this->showMotd($pl->getName(),$pl->getLevel()->getName());
	}
	public function onLevelChange(EntityLevelChangeEvent $ev) {
		$pl = $ev->getEntity();
		if (!($pl instanceof Player)) return;
		$level = $ev->getTarget()->getName();
		$this->showMotd($pl->getName(),$level);
	}
}
