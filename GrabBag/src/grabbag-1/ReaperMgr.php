<?php
namespace grabbag;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;


class ReaperMgr implements Listener {
	public $owner;
	public function __construct(Plugin $plugin) {
		$this->owner = $plugin;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	/**
	 * @priority LOW
	 */
	public function onPlayerDeath(PlayerDeathEvent $e) {
		$name = $e->getEntity()->getName();
		if (($msg = $this->owner->onPlayerDeath($name)) != "") {
			$e->setDeathMessage($msg);
		}
	}
}
