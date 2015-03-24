<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\WorldGenerator;

use pocketmine\level\generator\Generator;
use pocketmine\level\generator\GenerationChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\block\Block;

class IceGN extends Generator{
	private $level, $options, $random, $floatSeed;

	public function getSettings(){
		return $this->options;
	}

	public function getName(){
		return "ice";
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
				$chunk->setBlockId($x, 64, $z, 79);
			}
		}
		for($y = 1; $y < 63; $y++){
			for($z = 0; $z < 16; $z++){
				for($x = 0; $x < 16; $x++){
					$chunk->setBlockId($x, $y, $z, 8);
				}
			}
		}
	}

	public function populateChunk($chunkX, $chunkZ){}

	public function getSpawn(){
		return new Vector3(128, 3, 128);
	}
}