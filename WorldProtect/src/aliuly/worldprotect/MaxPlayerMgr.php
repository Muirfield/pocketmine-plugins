<?php
namespace aliuly\worldprotect;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\utils\TextFormat;
use pocketmine\Player;

class MaxPlayerMgr implements Listener {
	public $owner;
	public function __construct(Plugin $plugin) {
		$this->owner = $plugin;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	public function onTeleport(EntityTeleportEvent $ev){
		//echo __METHOD__.",".__LINE__."\n"; //##DEBUG
		if ($ev->isCancelled()) return;
		$et = $ev->getEntity();
		if (!($et instanceof Player)) return;

		$from = $ev->getFrom()->getLevel();
		$to = $ev->getTo()->getLevel();
		if (!($from && $to)) return;// We don't continue if anything is null

		$from = $from->getName();
		$to = $to->getName();

		//echo "FROM:$from TO:$to\n";//##DEBUG
		if ($from == $to) return;
		$max = $this->owner->getPlayerLimit($to);
		if ($max == 0) return;
		$np = count($this->owner->getServer()->getLevelByName($to)->getPlayers());
		if($np >= $max) {
			$ev->setCancelled();
			$et->sendMessage("Unable to teleport to $to\nWorld is full");
			$this->owner->getLogger()->info("$to is FULL");
		}
	}
}
