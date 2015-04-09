<?php
namespace aliuly\notsoflat;

use pocketmine\plugin\PluginBase;
use pocketmine\level\generator\Generator;

class Main extends PluginBase{
	public function onEnable(){
	  Generator::addGenerator(NotSoFlat::class, "notsoflat");
	}
}
