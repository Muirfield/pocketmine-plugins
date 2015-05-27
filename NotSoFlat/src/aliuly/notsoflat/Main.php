<?php
namespace aliuly\notsoflat;

use pocketmine\plugin\PluginBase;
use pocketmine\level\generator\Generator;
use pocketmine\utils\TextFormat;
use aliuly\common\MPMU;

class Main extends PluginBase{
	public function onEnable(){
		if (MPMU::pmCheck($this->getServer(),"1.12.0")) {
			$this->getLogger()->info(TextFormat::RED."WARNING: PMv1.5 support is experimental!");
			Generator::addGenerator(NotSoFlat::class, "notsoflat");
		} else {
			Generator::addGenerator(NotSoFlatOld::class, "notsoflat");
		}
	}
}
