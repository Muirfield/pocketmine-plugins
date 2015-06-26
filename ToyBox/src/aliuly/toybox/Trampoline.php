<?php
namespace aliuly\toybox;

use pocketmine\event\Listener;

use pocketmine\utils\TextFormat;
use pocketmine\block\Block;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\math\Vector3;


class Trampoline implements Listener {
	protected $blocks;

	public function __construct($plugin,$cfg) {
		$this->owner = $plugin;
		$this->blocks = [];
		if (isset($cfg["blocks"]) && is_array($cfg["blocks"])) {
			foreach ($cfg["blocks"] as $i) {
				$item = $this->owner->getItem($i,false,"trampoline");
				if ($item === null) continue;
				$this->blocks[$item->getId()] = $item->getId();
			}
		}
		if (count($this->blocks)) {
			$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
			$this->owner->getLogger()->info(TextFormat::GREEN."Trampoline blocks:".
												  count($this->blocks));
		} else {
			$this->getLogger()->info(TextFormat::RED."No blocks configured");
		}
	}

	public function onFall(EntityDamageEvent $ev) {
		if ($ev->isCancelled()) return;
		$cause = $ev->getCause();
		if ($cause !== EntityDamageEvent::CAUSE_FALL) return;
		$et = $ev->getEntity();
		$id = $et->getLevel()->getBlockIdAt($et->getX(),$et->getY()-1,$et->getZ());
		if (isset($this->blocks[$id])) {
			// Soft landing!
			$ev->setCancelled();
		}
	}

	public function onMove(PlayerMoveEvent $ev) {
		if ($ev->isCancelled()) return;
		$from = $ev->getFrom();
		$to = $ev->getTo();
		$dir = ["dx"=>$to->getX()-$from->getX(),
				  "dy"=>$to->getY()-$from->getY(),
				  "dz"=>$to->getZ()-$from->getZ()];
		if (!$dir["dy"]) return;
		$id = $to->getLevel()->getBlockIdAt($to->getX(),$to->getY()-1,$to->getZ());
		if (isset($this->blocks[$id])) {
			$ev->getPlayer()->setMotion(new Vector3($dir["dx"],-$dir["dy"]*1.1,$dir["dz"]));

		}
	}
}
