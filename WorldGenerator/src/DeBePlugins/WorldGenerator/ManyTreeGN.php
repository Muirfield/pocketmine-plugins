<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\WorldGenerator;

use pocketmine\level\generator\Generator;
use pocketmine\level\generator\GenerationChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\block\Block;

class ManyTreeGN extends Generator{
	private $level, $options, $random, $floatSeed;

	public function getSettings(){
		return $this->options;
	}

	public function getName(){
		return "manytree";
	}

	public function __construct(array $option = []){
		$this->options = [];
	}

	public function init(GenerationChunkManager $level, Random $random){
		$this->level = $level;
		$this->random = $random;
		$this->floatSeed = $this->random->nextFloat();
	}

	public function generateChunk($chunkX, $chunkZ){
		$this->random->setSeed((int) (($chunkX * 0xdead + $chunkZ * 0xbeef) * $this->floatSeed));
		$chunk = $this->level->getChunk($chunkX, $chunkZ);
		for($z = 0; $z < 16; $z++){
			for($x = 0; $x < 16; $x++){
				$chunk->setBlockId($x, 0, $z, 7);
				$chunk->setBlockId($x, 1, $z, 3);
				$chunk->setBlockId($x, 2, $z, 2);
			}
		}
		$rX = rand(0, 5);
		$rZ = rand(0, 5);
		$rrX = rand(6, 11);
		$rrZ = rand(6, 11);
		foreach([[0,3,1,18,12 ],[0,3,2,18,12 ],[0,3,3,18,12 ],[1,3,0,18,12 ],[1,3,1,18,12 ],[1,3,2,18,12 ],[1,3,3,18,12 ],[1,3,4,18,12 ],[1,4,1,18,12 ],[1,4,2,18,12 ],[1,4,3,18,12 ],[1,5,2,18,12 ],[2,0,2,17,0 ],[2,1,2,17,0 ],[2,2,2,17,0 ],[2,3,0,18,12 ],[2,3,1,18,12 ],[2,3,2,17,0 ],[2,3,3,18,12 ],[2,3,4,18,12 ],[2,4,1,18,12 ],[2,4,2,17,0 ],[2,4,3,18,12 ],[2,5,1,18,12 ],[2,5,2,18,12 ],[2,5,3,18,12 ],[3,3,0,18,12 ],[3,3,1,18,12 ],[3,3,2,18,12 ],[3,3,3,18,12 ],[3,3,4,18,12 ],[3,4,1,18,12 ],[3,4,2,18,12 ],[3,4,3,18,12 ],[3,5,2,18,12 ],[4,3,1,18,12 ],[4,3,2,18,12 ],[4,3,3,18,12 ] ] as $b){
			$chunk->setBlockId($b[0] + $rX, $b[1] + 3, $b[2] + $rZ, $b[3]);
			$chunk->setBlockData($b[0] + $rX, $b[1] + 3, $b[2] + $rZ, $b[4]);
			$chunk->setBlockId($b[0] + $rrX, $b[1] + 3, $b[2] + $rrZ, $b[3]);
			$chunk->setBlockData($b[0] + $rrX, $b[1] + 3, $b[2] + $rrZ, $b[4]);
		}
	}

	public function populateChunk($chunkX, $chunkZ){}

	public function getSpawn(){
		return new Vector3(128, 3, 128);
	}
}