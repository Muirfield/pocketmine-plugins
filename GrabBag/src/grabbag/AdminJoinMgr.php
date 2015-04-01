<?php
namespace grabbag;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\scheduler\CallbackTask;
use pocketmine\event\player\PlayerJoinEvent;

class AdminJoinMgr implements Listener {
  public $owner;
  static $delay;
  public function __construct(Plugin $plugin) {
    $this->owner = $plugin;
    $this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
    self::$delay = 15;
  }
  public function onPlayerJoin(PlayerJoinEvent $e) {
    $pl = $e->getPlayer();
    if ($pl == null) return;
    if (!$pl->isOp()) return;
    $task =new CallbackTask([$this,"announceOp"],[$pl->getName()]);
    $this->owner->getServer()->getScheduler()->scheduleDelayedTask($task,self::$delay);
  }
  public function announceOp($pn) {
    $this->owner->getServer()->broadcastMessage("Server op $pn joined");
  }
}
