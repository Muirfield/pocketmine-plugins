<?php
namespace aliuly\emptyworld;

use pocketmine\plugin\PluginBase;
use pocketmine\level\generator\Generator;


class Main extends PluginBase {
	public function onEnable() {
		Generator::addGenerator(WorldGen::class, WorldGen::NAME);
	}
}
