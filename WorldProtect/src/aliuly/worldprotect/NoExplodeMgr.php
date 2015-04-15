<?php
namespace aliuly\worldprotect;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\utils\TextFormat;

class NoExplodeMgr implements Listener {
	public $owner;
	public function __construct(Plugin $plugin) {
		$this->owner = $plugin;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	public function onExplode(EntityExplodeEvent $ev){
		//echo __METHOD__.",".__LINE__."\n";
		$et = $ev->getEntity();
		if ($this->owner->checkNoExplode($et->getX(),$et->getY(),$et->getZ(),
													$et->getLevel()->getName())) return;
		$ev->setCancelled();
		$this->owner->getLogger()->info(TextFormat::RED.
												  "Explosion was stopped in ".$et->getLevel()->getName());
	}
}
