<?php

namespace aliuly\notsoflat;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;
use pocketmine\level\generator\populator\Populator;

class DesertPlant extends Populator{
	/** @var ChunkManager */
	private $level;
	private $randomAmount;
	private $baseAmount;

	public function setRandomAmount($amount){
		$this->randomAmount = $amount;
	}

	public function setBaseAmount($amount){
		$this->baseAmount = $amount;
	}

	public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random){
		$this->level = $level;
		$amount = $random->nextRange(0, $this->randomAmount + 1) + $this->baseAmount;
		for($i = 0; $i < $amount; ++$i){
			$x = $random->nextRange($chunkX << 4, ($chunkX << 4) + 15);
			$z = $random->nextRange($chunkZ << 4, ($chunkZ << 4) + 15);
			for($size = 30; $size > 0; --$size){
				$xx = $x - 7 + $random->nextRange(0, 15);
				$zz = $z - 7 + $random->nextRange(0, 15);
				$yy = $this->getHighestWorkableBlock($xx, $zz);

				if($yy !== -1 and $this->canTallGrassStay($xx, $yy, $zz)){
					$this->level->setBlockIdAt($xx, $yy, $zz, Block::CACTUS);
					$this->level->setBlockDataAt($xx, $yy, $zz, 1);
				}
			}
		}
	}

	private function canTallGrassStay($x, $y, $z){
		return $this->level->getBlockIdAt($x, $y, $z) === Block::AIR and $this->level->getBlockIdAt($x, $y - 1, $z) === Block::SAND;
	}

	private function getHighestWorkableBlock($x, $z){
		for($y = 128; $y > 0; --$y){
			$b = $this->level->getBlockIdAt($x, $y, $z);
			if($b === Block::AIR or $b === Block::LEAVES){
				if(--$y <= 0){
					return -1;
				}
			}else{
				break;
			}
		}

		return ++$y;
	}
}
