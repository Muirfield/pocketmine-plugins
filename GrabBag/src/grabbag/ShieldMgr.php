<?php
namespace grabbag;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;

class ShieldMgr implements Listener {
  public $owner;
  public function __construct(Plugin $plugin) {
    $this->owner = $plugin;
    $this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
  }
  public function onDamage(EntityDamageEvent $ev) {
    if(!($ev instanceof EntityDamageByEntityEvent)) return;
    if (!($ev->getEntity() instanceof Player)) return;
    if ($this->owner->checkShield($ev->getEntity()->getName())) return;
    $ev->setCancelled();
  }
}
