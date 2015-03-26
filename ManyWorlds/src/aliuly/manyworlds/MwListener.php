<?php
namespace aliuly\manyworlds;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerMoveEvent;
//use pocketmine\event\entity\EntityDamageEvent; // Is this necessary?
//use pocketmine\event\player\PlayerInteractEvent; // Not used for now...

use pocketmine\Player;
use pocketmine\scheduler\CallbackTask;


class MwListener implements Listener {
  public $owner;

  public function __construct(Plugin $plugin) {
    $this->owner = $plugin;
    $this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
  }
  private function showMotd($name,$level,$ticks=10) {
    $this->owner->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this->owner,"showMotd"],[$name,$level]),$ticks);
  }
  public function onJoin(PlayerJoinEvent $ev) {
    $pl = $ev->getPlayer();
    $this->showMotd($pl->getName(),$pl->getLevel()->getName());
  }
  public function onLevelChange(EntityLevelChangeEvent $ev) {
    $pl = $ev->getEntity();
    if (!($pl instanceof Player)) return;
    $level = $ev->getTarget()->getName();
    $this->showMotd($pl->getName(),$level);
  }
  //
  // World protect
  //
  public function onBlockBreak(BlockBreakEvent $ev){
    $pl = $ev->getPlayer();
    if ($this->owner->checkBlockPlaceBreak($pl->getName(),
					   $pl->getLevel()->getName())) return;
    $pl->sendMessage("You are not allowed to do that here");
    $ev->setCancelled();
  }
  public function onBlockPlace(BlockPlaceEvent $ev){
    $pl = $ev->getPlayer();
    if ($this->owner->checkBlockPlaceBreak($pl->getName(),
					   $pl->getLevel()->getName())) return;
    $pl->sendMessage("You are not allowed to do that here");
    $ev->setCancelled();
  }
  public function onPvP(EntityDamageByEntityEvent $ev) {
    //if(!($eventPvP instanceof EntityDamageByEntityEvent)) return;
    if (!($ev->getEntity() instanceof Player && $ev->getDamager() instanceof Player)) return;
    if ($this->owner->checkPvP($ev->getEntity()->getLevel()->getName())) return;
    $ev->getDamager()->sendMessage("You are not allowed to do that here");
    $ev->setCancelled();
  }

  public function onPlayerMove(PlayerMoveEvent $ev) {
    $pl = $ev->getPlayer();
    $pos = $ev->getTo();
    if ($this->owner->checkMove($pl->getName(),$pl->getLevel()->getName(),
				$pos->getX(),$pos->getZ())) return;
    $pl->sendMessage("You have reached the end of the world");
    $ev->setCancelled();
  }
}