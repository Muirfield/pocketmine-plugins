<?php
namespace grabbag;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerInteractEvent;

class GrabBagListener implements Listener {
  public $owner;
  public function __construct(Plugin $plugin) {
    $this->owner = $plugin;
    $this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
  }
  public function onPlayerJoin(PlayerJoinEvent $e) {
    $this->owner->after(30,["onPlayerJoin",$e->getPlayer()->getName()]);
  }
  public function onRespawn(PlayerRespawnEvent $e) {
    //$this->owner->after(60,["respawnPlayer",$e->getPlayer()->getName()]);
    $this->owner->respawnPlayer($e->getPlayer()->getName());
  }
  /*
  private function worldProtect($player) {
    return $this->owner->worldProtect($player);
  }
  public function onBlockBreak(BlockBreakEvent $e)  {
    if ($this->worldProtect($e->getPlayer())) $e->setCancelled();
  }
  public function onBlockPlace(BlockPlaceEvent $e) {
    if ($this->worldProtect($e->getPlayer())) $e->setCancelled();
  }
  public function onPlayerInteract(PlayerInteractEvent $e) {
    if ($this->worldProtect($e->getPlayer())) $e->setCancelled();
    }*/
}
