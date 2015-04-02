<?php
namespace grabbag;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\scheduler\CallbackTask;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;

class SrvModeMgr implements Listener {
  public $owner;
  public $state;
  static $delay = 5;

  public function __construct(Plugin $plugin) {
    $this->owner = $plugin;
    $this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
    $this->state = false;
  }
  public function getMode() { return $this->state; }
  public function setMode($state = false) {
    $this->state = $state;
    if ($state) {
      $this->owner->getServer()->broadcastMessage("ATTENTION: Entering service mode");
      $this->owner->getServer()->broadcastMessage(" - ".$state);
    } else {
      $this->owner->getServer()->broadcastMessage("ATTENTION: Leaving service mode");
    }
  }
  public function onPlayerJoin(PlayerJoinEvent $e) {
    $pl = $e->getPlayer();
    if ($pl == null) return;
    if (!$this->state) return;
    if ($pl->hasPermission("gb.servicemode.allow")) {
      $task =new CallbackTask([$this,"announce"],[$pl->getName()]);
    } else {
      $task =new CallbackTask([$this,"kickuser"],[$pl->getName()]);
    }
    $this->owner->getServer()->getScheduler()->scheduleDelayedTask($task,self::$delay);
  }
  public function announce($pn) {
    $player = $this->owner->getServer()->getPlayer($pn);
    if (!($player instanceof Player)) return;
    $player->sendMessage("NOTE: currently in service mode");
    $player->sendMessage("- ".$this->state);
  }
  public function kickuser($pn) {
    $player = $this->owner->getServer()->getPlayer($pn);
    if (!($player instanceof Player)) return;
    $this->owner->getServer()->broadcastMessage("$pn attempted to join");
    $player->kick($this->state);
  }
}
