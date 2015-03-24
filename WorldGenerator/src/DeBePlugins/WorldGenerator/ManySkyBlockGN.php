<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\WorldGenerator;

use pocketmine\level\generator\Generator;
use pocketmine\level\generator\GenerationChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\block\Block;

class ManySkyBlockGN extends Generator{
	private $level, $options, $random, $floatSeed;

	public function getSettings(){
		return [];
	}

	public function getName(){
		return "manyskyblock";
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
		foreach([[0,0,0,3,0 ],[0,0,1,3,0 ],[0,0,2,3,0 ],[0,0,3,3,0 ],[0,0,4,3,0 ],[0,0,5,3,0 ],[0,1,0,3,0 ],[0,1,1,3,0 ],[0,1,2,3,0 ],[0,1,3,3,0 ],[0,1,4,3,0 ],[0,1,5,3,0 ],[0,2,0,2,0 ],[0,2,1,2,0 ],[0,2,2,2,0 ],[0,2,3,2,0 ],[0,2,4,2,0 ],[0,2,5,2,0 ],[0,6,2,18,12 ],[0,6,3,18,12 ],[0,6,4,18,12 ],[0,6,5,18,12 ],[0,6,6,18,12 ],[0,7,3,18,12 ],[0,7,4,18,12 ],[0,7,5,18,12 ],[0,8,4,18,12 ],[1,0,0,3,0 ],[1,0,1,7,0 ],[1,0,2,3,0 ],[1,0,3,3,0 ],[1,0,4,3,0 ],[1,0,5,3,0 ],[1,1,0,3,0 ],[1,1,1,3,0 ],[1,1,2,3,0 ],[1,1,3,3,0 ],[1,1,4,3,0 ],[1,1,5,3,0 ],[1,2,0,2,0 ],[1,2,1,2,0 ],[1,2,2,2,0 ],[1,2,3,2,0 ],[1,2,4,3,0 ],[1,2,5,2,0 ],[1,3,4,17,0 ],[1,4,4,17,0 ],[1,5,4,17,0 ],[1,6,2,18,12 ],[1,6,3,18,12 ],[1,6,4,17,0 ],[1,6,5,18,12 ],[1,6,6,18,12 ],[1,7,3,18,12 ],[1,7,4,17,0 ],[1,7,5,18,12 ],[1,8,3,18,12 ],[1,8,4,18,12 ],[1,8,5,18,12 ],[2,0,0,3,0 ],[2,0,1,3,0 ],[2,0,2,3,0 ],[2,0,3,3,0 ],[2,0,4,3,0 ],[2,0,5,3,0 ],[2,1,0,3,0 ],[2,1,1,3,0 ],[2,1,2,3,0 ],[2,1,3,3,0 ],[2,1,4,3,0 ],[2,1,5,3,0 ],[2,2,0,2,0 ],[2,2,1,2,0 ],[2,2,2,2,0 ],[2,2,3,2,0 ],[2,2,4,2,0 ],[2,2,5,2,0 ],[2,6,2,18,12 ],[2,6,3,18,12 ],[2,6,4,18,12 ],[2,6,5,18,12 ],[2,6,6,18,12 ],[2,7,3,18,12 ],[2,7,4,18,12 ],[2,7,5,18,12 ],[2,8,4,18,12 ],[3,0,0,3,0 ],[3,0,1,3,0 ],[3,0,2,3,0 ],[3,1,0,3,0 ],[3,1,1,3,0 ],[3,1,2,3,0 ],[3,2,0,2,0 ],[3,2,1,2,0 ],[3,2,2,2,0 ],[3,6,3,18,12 ],[3,6,4,18,12 ],[3,6,5,18,12 ],[4,0,0,3,0 ],[4,0,1,3,0 ],[4,0,2,3,0 ],[4,1,0,3,0 ],[4,1,1,3,0 ],[4,1,2,3,0 ],[4,2,0,2,0 ],[4,2,1,2,0 ],[4,2,2,2,0 ],[5,0,0,3,0 ],[5,0,1,3,0 ],[5,0,2,3,0 ],[5,1,0,3,0 ],[5,1,1,3,0 ],[5,1,2,3,0 ],[5,2,0,2,0 ],[5,2,1,2,0 ],[5,2,2,2,0 ] ] as $b){
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