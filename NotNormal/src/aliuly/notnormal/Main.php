<?php
namespace aliuly\notnormal;

use pocketmine\plugin\PluginBase;
use pocketmine\level\generator\Generator;

class Main extends PluginBase{
  public function onEnable(){
    Generator::addGenerator(NotNormal::class, "notnormal");
  }
}