<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\WorldGenerator;

use pocketmine\level\generator\Generator;
use pocketmine\level\generator\GenerationChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\block\Block;

class MultySpecialSkyBlockGN extends Generator{
	private $level, $options, $random, $floatSeed;

	public function getSettings(){
		return [];
	}

	public function getName(){
		return "multyspecialskyblock";
	}

	public function __construct(array $option = []){}

	public function init(GenerationChunkManager $level, Random $random){
		$this->level = $level;
		$this->random = $random;
		$this->floatSeed = $this->random->nextFloat();
	}

	public function generateChunk($chunkX, $chunkZ){
		$chunk = $this->level->getChunk($chunkX, $chunkZ);
		if($chunk->getX() % 4 !== 0 || $chunk->getZ() % 4 !== 0) return;
		for($z = 0; $z < 16; $z++){
			for($x = 0; $x < 16; $x++){
				$chunk->setBlockId($x, 32, $z, 3);
				$chunk->setBlockId($x, 33, $z, 3);
				$chunk->setBlockId($x, 34, $z, 2);
			}
		}
		foreach([[5,5,7,18,12 ],[5,5,8,18,12 ],[5,5,9,18,12 ],[5,6,7,18,12 ],[5,6,8,18,12 ],[5,6,9,18,12 ],[5,7,8,18,12 ],[6,5,6,18,12 ],[6,5,7,18,12 ],[6,5,8,18,12 ],[6,5,9,18,12 ],[6,5,10,18,12 ],[6,6,6,18,12 ],[6,6,7,18,12 ],[6,6,8,18,12 ],[6,6,9,18,12 ],[6,6,10,18,12 ],[6,7,7,18,12 ],[6,7,8,18,12 ],[6,7,9,18,12 ],[6,8,8,18,8 ],[7,0,7,7,0 ],[7,0,8,7,0 ],[7,3,6,26,0 ],[7,3,7,26,8 ],[7,3,8,17,0 ],[7,4,8,17,0 ],[7,5,6,18,12 ],[7,5,7,18,12 ],[7,5,8,17,0 ],[7,5,9,18,12 ],[7,5,10,18,12 ],[7,6,6,18,12 ],[7,6,7,18,12 ],[7,6,8,17,0 ],[7,6,9,18,12 ],[7,6,10,18,12 ],[7,7,6,18,12 ],[7,7,7,18,12 ],[7,7,8,17,0 ],[7,7,9,18,12 ],[7,7,10,18,12 ],[7,8,7,18,8 ],[7,8,8,18,8 ],[7,8,9,18,8 ],[8,0,7,7,0 ],[8,0,8,7,0 ],[8,3,7,61,2 ],[8,3,8,58,0 ],[8,4,7,54,2 ],[8,4,8,245,0 ],[8,5,6,18,12 ],[8,5,7,18,12 ],[8,5,8,18,12 ],[8,5,9,18,12 ],[8,5,10,18,12 ],[8,6,6,18,12 ],[8,6,7,18,12 ],[8,6,8,18,12 ],[8,6,9,18,12 ],[8,6,10,18,12 ],[8,7,7,18,12 ],[8,7,8,18,12 ],[8,7,9,18,12 ],[8,8,8,18,8 ],[9,5,7,18,12 ],[9,5,8,18,12 ],[9,5,9,18,12 ],[9,6,7,18,12 ],[9,6,8,18,12 ],[9,6,9,18,12 ],[9,7,8,18,12 ],[11,3,0,3,0 ],[11,3,1,3,0 ],[11,3,2,3,0 ],[12,2,1,2,0 ],[12,3,0,3,0 ],[12,3,1,9,0 ],[12,3,2,3,0 ],[13,3,0,3,0 ],[13,3,1,3,0 ],[13,3,2,3,0 ],[14,2,1,2,0 ],[14,3,0,3,0 ],[14,3,1,11,0 ],[14,3,2,2,0 ],[15,3,0,3,0 ],[15,3,1,3,0 ],[15,3,2,3,0 ] ] as $b){
			$chunk->setBlockId($b[0], $b[1] + 32, $b[2], $b[3]);
			$chunk->setBlockData($b[0], $b[1] + 32, $b[2], $b[4]);
		}
		$this->random->setSeed((int) (($chunkX * 0xdead + $chunkZ * 0xbeef) * $this->floatSeed));
	}

	public function populateChunk($chunkX, $chunkZ){}

	public function getSpawn(){
		return new Vector3(132, 40, 132);
	}
}