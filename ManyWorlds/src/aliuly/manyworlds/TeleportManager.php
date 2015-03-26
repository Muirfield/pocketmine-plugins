<?php
namespace aliuly\manyworlds;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\CallbackTask;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;

class TeleportManager implements Listener {
  public $owner;
  protected $teleporters = [];

  public function __construct(PluginBase $plugin) {
    $this->owner = $plugin;
    $this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
  }
  public function onDamage(EntityDamageEvent $event) {
    // Try keep the player alive while on transit...
    $victim= $event->getEntity();
    if (!($victim instanceof Player)) return;
    $vname = $victim->getName();
    if (!isset($this->teleporters[$vname])) return;
    if (time() - $this->teleporters[$vname] > 2) {
      unset($this->teleporters[$vname()]);
      return;
    }
    $victim->heal($event->getDamage());
    $event->setDamage(0);
    $event->setCancelled(true);
  }
  public function teleport($player,$level,$spawn=null) {
    $world = $this->owner->getServer()->getLevelByName($level);
    // Try to find a reasonable spawn location
    $location = $world->getSafeSpawn($spawn);
    $this->teleporters[$player->getName()] = time();
    foreach ([5,10,20] as $ticks) {
      // Try to keep the player in place until the chunk finish loading
      $this->after("delayedTP",[$player->getName(),
				$location->getX(),$location->getY(),
				$location->getZ()],$ticks);
    }
    // Make sure that any damage he may have taken is restored
    $this->after("restoreHealth",[$player->getName(),$player->getHealth()],20);
    // Make sure the player survives the transfer...
    $player->setHealth($player->getMaxHealth());
    $player->teleport($location); // Start the teleport
  }
  public function after($method,$args,$ticks) {
    $this->owner->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this,$method],$args),$ticks);
  }
  public function restoreHealth($name,$health) {
    $player = $this->owner->getServer()->getPlayer($name);
    if (!$player) return;
    $player->setHealth($health);
  }
  public function delayedTP($name,$x,$y,$z) {
    $player = $this->owner->getServer()->getPlayer($name);
    if (!$player) return;
    $player->teleport(new Vector3($x,$y,$z));
  }


}