<?php
namespace aliuly\notsoflat;

use pocketmine\plugin\PluginBase;
use pocketmine\level\generator\Generator;
use pocketmine\utils\TextFormat;

class Main extends PluginBase{
	public function onEnable(){
		$api = $this->getServer()->getApiVersion();
		if (version_compare($api,"1.12.0") >= 0) {
			$this->getLogger()->info(TextFormat::RED."WARNING: PMv1.5 support is experimental!");
			Generator::addGenerator(NotSoFlat::class, "notsoflat");
		} else {
			Generator::addGenerator(NotSoFlatOld::class, "notsoflat");
		}
	}
}
