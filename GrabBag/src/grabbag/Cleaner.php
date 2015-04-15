<?php
namespace grabbag;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;

class Cleaner implements Listener {
	public $owner;
	public function __construct(Plugin $plugin) {
		$this->owner = $plugin;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	public function onPlayerQuit(PlayerQuitEvent $ev) {
		$this->owner->cleanup($ev->getPlayer()->getName());
	}
}
