<?php
namespace aliuly\chickens;

use pocketmine\plugin\PluginBase;
use pocketmine\block\Block;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\entity\Entity;
use aliuly\chickens\Chicken;

class Main extends PluginBase implements Listener{
  const SPAWN_EGG = 383;
  public function onEnable(){
    Block::$creative[] = [ self::SPAWN_EGG, Chicken::NETWORK_ID ];
    Entity::registerEntity(Chicken::class);
  }
}
