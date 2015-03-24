<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\WorldGenerator;

use pocketmine\level\generator\Generator;
use pocketmine\level\generator\GenerationChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\block\Block;

class OreFlatGN extends Generator{
	private $level, $options, $random, $floatSeed;

	public function getSettings(){
		return $this->options;
	}

	public function getName(){
		return "oreflat";
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
		$list = [1 => 500,16 => 30,15 => 15,73 => 12,14 => 10,21 => 10,56 => 5 ];
		$blocks = [];
		foreach($list as $k => $v){
			for($f = 0; $f < $v; $f++)
				$blocks[] = $k;
		}
		for($z = 0; $z < 16; $z++){
			for($x = 0; $x < 16; $x++){
				$chunk->setBlockId($x, 0, $z, 7);
				$chunk->setBlockId($x, 62, $z, 3);
				$chunk->setBlockId($x, 63, $z, 3);
				$chunk->setBlockId($x, 64, $z, 2);
			}
		}
		for($y = 1; $y < 61; $y++){
			for($z = 0; $z < 16; $z++){
				for($x = 0; $x < 16; $x++){
					$chunk->setBlockId($x, $y, $z, $blocks[array_rand($blocks)]);
				}
			}
		}
	}

	public function populateChunk($chunkX, $chunkZ){}

	public function getSpawn(){
		return new Vector3(128, 3, 128);
	}
}