<?php
namespace DeBePlugins\WorldGenerator;
// This Plugin is Made by DeBe (hu6677@naver.com)
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\GenerationChunkManager;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\block\Block;

class SkyGridGN extends Generator{
	private $level, $options, $random, $floatSeed;

	public function getSettings(){
		return $this->options;
	}

	public function getName(){
		return "skygrid";
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
		$list = [1 => 500,2 => 100,3 => 100,8 => 50,10 => 30,12 => 50,13 => 50,14 => 10,15 => 15,16 => 30,17 => 50,18 => 50,21 => 10,30 => 20,35 => 100,48 => 50,49 => 50,56 => 5,73 => 10,74 => 10,79 => 40,80 => 20,82 => 30,86 => 30,103 => 30,110 => 50,129 => 10,243 => 40 ];
		$blocks = [];
		foreach($list as $k => $v){
			for($f = 0; $f < $v; $f++)
				$blocks[] = $k;
		}
		for($y = 0; $y < 64; $y += 3){
			for($z = 0; $z < 16; $z += 3){
				for($x = 0; $x < 16; $x += 3){
					$id = $blocks[array_rand($blocks)];
					$chunk->setBlockId($x, $y, $z, $id);
					if($id == 35) $chunk->setBlockData($x, $y, $z, rand(0, 15));
				}
			}
		}
	}

	public function populateChunk($chunkX, $chunkZ){}

	public function getSpawn(){
		return new Vector3(128, 3, 128);
	}
}