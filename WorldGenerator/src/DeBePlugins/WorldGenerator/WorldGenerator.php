<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\WorldGenerator;

use pocketmine\plugin\PluginBase;
use pocketmine\level\generator\Generator;

class WorldGenerator extends PluginBase{

	public function onEnable(){
		Generator::addGenerator(ApartGN::class, "Apart");
		Generator::addGenerator(Ice::class, "Ice");
		Generator::addGenerator(Lava::class, "Lava");
		Generator::addGenerator(ManySkyBlockGN::class, "ManySkyBlock");
		Generator::addGenerator(ManyTreeGN::class, "ManyTree");
		Generator::addGenerator(MultySkyBlockGN::class, "MultySkyBlock");
		Generator::addGenerator(MultySpecialSkyBlockGN::class, "MultySpecialSkyBlock");
		Generator::addGenerator(NoneGN::class, "None");
		Generator::addGenerator(OreFlatGN::class, "OreFlat");
		Generator::addGenerator(OreTreeFlatGN::class, "OreTreeFlat");
		Generator::addGenerator(SkyBlockGN::class, "SkyBlock");
		Generator::addGenerator(SkyGridGN::class, "SkyGrid");
		Generator::addGenerator(TreeGN::class, "Tree");
		Generator::addGenerator(WaterGN::class, "Water");
		Generator::addGenerator(WhiteWayGN::class, "WhiteWay");
	}
}