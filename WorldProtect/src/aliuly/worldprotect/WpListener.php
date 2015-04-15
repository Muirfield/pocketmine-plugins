<?php
namespace aliuly\worldprotect;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\event\level\LevelLoadEvent;
use pocketmine\event\level\LevelUnloadEvent;


class WpListener implements Listener {
	public function __construct(Plugin $plugin) {
		$this->owner = $plugin;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	// Make sure configs are loaded/unloaded
	public function onLevelLoad(LevelLoadEvent $e) {
		$this->owner->loadWorldConfig($e->getLevel()->getName());
	}
	public function onLevelUnload(LevelUnloadEvent $e) {
		$this->owner->unloadWorldConfig($e->getLevel()->getName());
	}
}
