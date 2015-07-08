<?php
namespace aliuly\toybox;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\math\Vector3;
use pocketmine\item\Item;
use aliuly\toybox\common\mc;
use aliuly\toybox\common\PluginCallbackTask;

class CompassTp implements Listener {
	public $owner;
	protected $item;

	public function __construct($plugin,$i) {
		$this->owner = $plugin;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$this->item = $this->owner->getItem($i,Item::COMPASS,"compassTp")->getId();
	}
	public function delayedTP($name,$x,$y,$z) {
		$pl = $this->owner->getServer()->getPlayer($name);
		if (!$pl) return;
		$pl->teleport(new Vector3($x,$y,$z));
	}
	public function onPlayerInteract(PlayerInteractEvent $e) {
		// Implement the CompassTP thingie...
		$pl = $e->getPlayer();

		if (!$pl->hasPermission("toybox.compasstp")) return;

		$hand = $pl->getInventory()->getItemInHand();
		if ($hand->getID() != $this->item) return;

		$pos = $pl->getPosition()->add(0,$pl->getEyeHeight(),0);
		$start = new Vector3($pos->getX(),$pos->getY(),$pos->getZ());

		$lv = $pl->getLevel();
		for ($start = new Vector3($pos->getX(),$pos->getY(),$pos->getZ());
			  $start->distance($pos) < 120;
			  $pos=$pos->add($pl->getDirectionVector())) {
			$block = $lv->getBlock($pos->floor());
			if ($block->getId() != 0) break;
		}
		if ($block->getId() == 0) {
			$pl->sendMessage(mc::_("Can not teleport to the void!"));
			return;
		}
		$pos=$pos->subtract($pl->getDirectionVector());
		$dist = $start->distance($pos);
		if ($dist < 2.8) {
			$pl->sendMessage(mc::_("Not teleporting..."));
			$pl->sendMessage(mc::_("You could easily walk there!"));
			return;
		}
		//echo "Block: ".$block->getName()." (".$block->getId().")\n";
		//print_r($pos);
		//echo "Distance: ".$start->distance($pos)."\n";
		$pl->sendMessage(mc::_("Teleporting... %1%",intval($dist)));
		$pos = $pos->add(0,1,0);

		/*$cb = new CallbackTask([$this,"delayedTP"],[$pl->getName(),
		  $pos->getX(),
		  $pos->getY(),
		  $pos->getZ()]);
		  $this->owner->getServer()->getScheduler()->scheduleDelayedTask($cb,20);
		  //$pl->teleport($pos);
		  return;*/
		$m = 5.0;
		for ($f = 1.0; $f <= $m; $f++) {
			$ticks = intval($f) * 5;
			$x = (($pos->getX()-$start->getX())*$f/$m)+$start->getX();
			$y = (($pos->getY()-$start->getY())*$f/$m)+$start->getY();
			$z = (($pos->getZ()-$start->getZ())*$f/$m)+$start->getZ();
			$c = new PluginCallbackTask($this->owner,[$this,"delayedTP"],[$pl->getName(),$x,$y,$z]);
			$this->owner->getServer()->getScheduler()->scheduleDelayedTask($c,$ticks);
		}
	}
}
