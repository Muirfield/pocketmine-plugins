<?php
namespace aliuly\emptyworld;

use pocketmine\plugin\PluginBase;
use pocketmine\level\generator\Generator;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\level\generator\biome\Biome;
// This one is for PocketMine 1.4
//use pocketmine\level\generator\GenerationChunkManager as ChunkManager;
// This one is for PocketMine 1.5
use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;

class WorldGen extends Generator {
	const NAME = "emptyworld";
	private $options;
	private $block, $radius, $floorLevel,$biome,$baseFloor;
	/** @var ChunkManager */
	private $level;
	/** @var Random */
	private $random;
	/** @var FullChunk Chunk master */
	private $chunk;

	public function __construct(array $options = []) {
		$this->block = Block::STONE;
		$this->radius = 10;
		$this->floorLevel = 64;
		$this->biome = 1;
		$this->options = $options;
		$this->chunk  = null;
		$this->baseFloor = Block::AIR;

	}
	public function init(ChunkManager $level, Random $random) {
		$this->level = $level;
		$this->random = $random;
		// Parse options...
		if (isset($this->options["preset"])) {
			$preset = ",".strtolower($this->options["preset"]).",";
			if (preg_match('/,\s*block\s*=\s*(\d+)\s*,/',$preset,$mv)) {
				$this->block = intval($mv[1]);
			}
			if (preg_match('/,\s*radius\s*=\s*(\d+)\s*,/',$preset,$mv)) {
				$this->radius = intval($mv[1]);
			}
			if (preg_match('/,\s*floorlevel\s*=\s*(\d+)\s*,/',$preset,$mv)) {
				$this->floorLevel = intval($mv[1]);
			}
			if (preg_match('/,\s*biome\s*=\s*(\d+)\s*,/',$preset,$mv)) {
				$this->biome = intval($mv[1]);
			}
			if (preg_match('/,\s*basefloor\s*=\s*(\d+)\s*,/',$preset,$mv)) {
				$this->baseFloor = intval($mv[1]);
			}
		}
	}
	protected static function inSpawn($rad2,$spx,$spz,$x,$z){
		$xoff = $spx - $x;
		$zoff = $spz - $z;
		return $rad2 > ($xoff * $xoff + $zoff * $zoff);
	}
	public function generateChunk($chunkX, $chunkZ) {
		$spawn = $this->getSpawn();

		// Adjust spawn so it is relative to current chunk...
		$spawn->x = $spawn->x - ($chunkX << 4);
		$spawn->z = $spawn->z - ($chunkZ << 4);

		$inSpawn = (-$this->radius <= $spawn->x && $spawn->x < 16 + $this->radius  &&
				-$this->radius <= $spawn->z && $spawn->z < 16 + $this->radius);

		if ($inSpawn || $this->chunk === null) {
			$chunk = $this->level->getChunk($chunkX,$chunkZ);
			$chunk->setGenerated();

			$c = Biome::getBiome($this->biome)->getColor();
			$R = $c >> 16;
			$G = ($c >> 8) & 0xff;
			$B = $c & 0xff;

			$rad2 = $this->radius * $this->radius;

			for($Z = 0; $Z < 16; ++$Z){
				for($X = 0; $X < 16; ++$X){
					$chunk->setBiomeId($X, $Z, $this->biome);
					$chunk->setBiomeColor($X, $Z, $R, $G, $B);
					$chunk->setBlock($X, 0, $Z, $this->baseFloor, 0);
					for($y = 1; $y < 128; ++$y){
						$chunk->setBlock($X, $y, $Z, Block::AIR, 0);
					}
					if (self::inSpawn($rad2,$spawn->x,$spawn->z,$X,$Z)) {
						$chunk->setBlock($X,$this->floorLevel,$Z,$this->block);
					}
				}
			}
			if (!$inSpawn) {
				$this->chunk = clone $chunk;
			}
		} else {
			$chunk = clone $this->chunk;
		}
		$chunk->setX($chunkX);
		$chunk->setZ($chunkZ);
		$this->level->setChunk($chunkX,$chunkZ,$chunk);
	}

	public function populateChunk($chunkX, $chunkZ) {
		// Don't do nothing here...
	}
	public function getSettings() {
		return $this->options;
	}
	public function getName() {
		return self::NAME;
	}
	public function getSpawn() {
		return new Vector3(128,$this->floorLevel+1,128);
	}
}
