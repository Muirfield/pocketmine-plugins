<?php
namespace pocketmine\block;
use pocketmine\item\Item;

class PoweredRail extends Transparent{
  protected $id = self::POWERED_RAIL;
  public function __construct($meta = 0){
    $this->meta = $meta;
  }
  public function getHardness(){
    return 8;
  }
  public function getName(){
    return "Powered Rail";
  }
  public function getBreakTime(Item $item){
    switch($item->isPickaxe()){
    case 5:
      return 0.1312;
    case 4:
      return 0.175;
    case 3:
      return 0.2625;
    case 2:
      return 0.0875;
    case 1:
      return 0.525;
    default:
      return 1.05;
    }
  }


}
