<?php
namespace aliuly\worldprotect;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\Player;


class WpBordersMgr implements Listener {
	public $owner;
	public function __construct(Plugin $plugin) {
		$this->owner = $plugin;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	public function onPlayerMove(PlayerMoveEvent $ev) {
		if ($ev->isCancelled()) return;
		$pl = $ev->getPlayer();
		$pos = $ev->getTo();
		if ($this->owner->checkMove($pl->getLevel()->getName(),
											 $pos->getX(),$pos->getZ())) return;
		$this->owner->msg($pl,"You have reached the end of the world");
		$ev->setCancelled();
	}
}
