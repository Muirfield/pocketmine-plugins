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
use pocketmine\event\entity\EntityDamageEvent;
//use pocketmine\event\player\PlayerInteractEvent; // Not used for now...
use pocketmine\event\level\LevelLoadEvent;
use pocketmine\event\level\LevelUnloadEvent;



use pocketmine\Player;
use pocketmine\scheduler\CallbackTask;


class MwListener implements Listener {
  public $owner;
  public $spam;
  const SPAM_DELAY = 5;

  private function msg($pl,$txt) {
    $n = $pl->getName();
    if (isset($this->spam[$n])) {
      // Check if we are spamming...
      if (time() - $this->spam[$n][0] < self::SPAM_DELAY
	  && $this->spam[$n][1] == $txt) return;
    }
    $this->spam[$n] = [ time(), $txt ];
    $pl->sendMessage($txt);
  }

  public function __construct(Plugin $plugin) {
    $this->owner = $plugin;
    $this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
    $this->spam = [];
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

  // Make sure configs are loaded/unloaded
  public function onLevelLoad(LevelLoadEvent $e) {
    $this->owner->loadWorldConfig($e->getLevel());
  }
  public function onLevelUnload(LevelUnloadEvent $e) {
    $this->owner->unloadWorld($e->getLevel()->getName());
  }

  //
  // World protect
  //
  public function onBlockBreak(BlockBreakEvent $ev){
    $pl = $ev->getPlayer();
    if ($this->owner->checkBlockPlaceBreak($pl->getName(),
					   $pl->getLevel()->getName())) return;
    $this->msg($pl,"You are not allowed to do that here");
    $ev->setCancelled();
  }
  public function onBlockPlace(BlockPlaceEvent $ev){
    $pl = $ev->getPlayer();
    if ($this->owner->checkBlockPlaceBreak($pl->getName(),
					   $pl->getLevel()->getName())) return;
    $this->msg($pl,"You are not allowed to do that here");
    $ev->setCancelled();
  }

  public function onPvP(EntityDamageEvent $ev) {
    if(!($ev instanceof EntityDamageByEntityEvent)) return;
    if (!($ev->getEntity() instanceof Player && $ev->getDamager() instanceof Player)) return;
    if ($this->owner->checkPvP($ev->getEntity()->getLevel()->getName())) return;
    $this->msg($ev->getDamager(),"You are not allowed to do that here");
    $ev->setCancelled();
  }

  public function onPlayerMove(PlayerMoveEvent $ev) {
    $pl = $ev->getPlayer();
    $pos = $ev->getTo();
    if ($this->owner->checkMove($pl->getName(),$pl->getLevel()->getName(),
				$pos->getX(),$pos->getZ())) return;
    $this->msg($pl,"You have reached the end of the world");
    $ev->setCancelled();
  }
}