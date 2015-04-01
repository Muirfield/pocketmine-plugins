<?php
namespace grabbag;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\scheduler\CallbackTask;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;

class CompassTpMgr implements Listener {
  public $owner;
  public function __construct(Plugin $plugin) {
    $this->owner = $plugin;
    $this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
  }
  public function delayedTP($name,$x,$y,$z) {
    $pl = $this->owner->getServer()->getPlayer($name);
    if (!$pl) return;
    $pl->teleport(new Vector3($x,$y,$z));
  }
  public function onPlayerInteract(PlayerInteractEvent $e) {
    // Implement the CompassTP thingie...
    $pl = $e->getPlayer();
    if (!$this->owner->canCompassTp($pl->getName())) return;
    $hand = $pl->getInventory()->getItemInHand();
    if ($hand->getID() != Item::COMPASS) return;

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
      $pl->sendMessage("Can not teleport to the void!");
      return;
    }
    //echo "Block: ".$block->getName()." (".$block->getId().")\n";
    //print_r($pos);
    //echo "Distance: ".$start->distance($pos)."\n";
    $pl->sendMessage("Teleporting... ".intval($start->distance($pos)));
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
      $c = new CallbackTask([$this,"delayedTP"],[$pl->getName(),$x,$y,$z]);
      $this->owner->getServer()->getScheduler()->scheduleDelayedTask($c,$ticks);
    }
  }
}
