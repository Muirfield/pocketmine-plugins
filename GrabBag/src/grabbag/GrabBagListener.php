<?php
namespace grabbag;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerDeathEvent;

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
    $this->owner->respawnPlayer($e->getPlayer()->getName());
  }
  public function onPlayerDeath(PlayerDeathEvent $e) {
    $name = $e->getEntity()->getName();
    if (($msg = $this->owner->onPlayerDeath($name)) != "") {
      $e->setDeathMessage($msg);
    }
  }
}
