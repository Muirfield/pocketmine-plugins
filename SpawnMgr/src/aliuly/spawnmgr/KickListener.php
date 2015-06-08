<?php
namespace aliuly\spawnmgr;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase as Plugin;

use pocketmine\event\player\PlayerKickEvent;
use pocketmine\Player;

class KickListener implements Listener {
	protected $reserved;
	public function __construct(Plugin $plugin,$cfg) {
		$this->owner = $plugin;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$this->reserved = $cfg;
	}
	public function onPlayerKick(PlayerKickEvent $event){
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
		//print_r($event->getReason());//##DEBUG
		if ($event->getReason() == "server full" ||
			 $event->getReason() == "disconnectionScreen.serverFull") {
			if (!$event->getPlayer()->hasPermission("spawnmgr.reserved"))
				return;
			if($this->reserved !== true) {
				// OK, we do have a limit...
				if(count($this->owner->getServer()->getOnlinePlayers()) >
					$this->owner->getServer()->getMaxPlayers() + $this->reserved) return;
			}
			$event->setCancelled();
			return;
		}
		// Not server full message...
	}
}
