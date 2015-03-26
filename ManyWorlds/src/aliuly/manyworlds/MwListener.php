<?php
namespace ManyWorlds;
use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\Player;


class MwListener implements Listener {
  public $owner;

  public function __construct(Plugin $plugin) {
    $this->owner = $plugin;
    $this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
  }
  public function onJoin(PlayerJoinEvent $ev) {
    $this->owner->onJoin($ev->getPlayer()->getName());
  }
  public function onLevelChange(EntityLevelChangeEvent $ev) {
    $pl = $ev->getEntity();
    if (!($pl instanceof Player)) return;
    $level = $ev->getTarget()->getName();
    $this->owner->after(new MwTask($this->owner,"showMotd",[$pl->getName(),$level]),21);
  }
  public function onDamage(EntityDamageEvent $event) {
    // Try keep the player alive while on transit...
    $victim= $event->getEntity();
    if (!($victim instanceof Player)) return;
    if (!$this->owner->onDamage($victim->getName(),$event->getDamage())) return;
    $event->setDamage(0);
    $event->setCancelled(true);
  }
}