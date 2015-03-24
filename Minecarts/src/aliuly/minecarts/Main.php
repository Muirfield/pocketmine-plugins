<?php
namespace aliuly\minecarts;

use pocketmine\plugin\PluginBase;
use pocketmine\block\Block;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use aliuly\minecarts\entity\Minecart as MinecartEntity;
use aliuly\minecarts\item\Minecart as MinecartItem;

// Create Minecart entity
// 


class Main extends PluginBase implements Listener{
  const RAILS = 66;
  const POWERED_RAILS = 27;
  const MINECART = 328; // pocketmine/item/Item.php defines MINECART wrongly!

  public function onEnable(){
    Block::$creative[] = [self::RAILS , 0];
    Block::$creative[] = [self::POWERED_RAILS, 0];
    Block::$creative[] = [self::MINECART , 0];

    Block::$list[self::RAILS] = Rails::class;
    //Block::$list[self::POWERED_RAILS] = PoweredRails::class;
    Item::$list[self::MINECART] = MinecartItem::class;
    Entity::registerEntity(MinecartEntity::class);
  }
}
