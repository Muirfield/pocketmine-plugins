<?php
namespace aliuly\notsoflat;

use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\block\CoalOre;
use pocketmine\block\DiamondOre;
use pocketmine\block\Dirt;
use pocketmine\block\GoldOre;
use pocketmine\block\Gravel;
use pocketmine\block\IronOre;
use pocketmine\block\LapisOre;
use pocketmine\block\RedstoneOre;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\noise\Simplex;
use pocketmine\level\generator\object\OreType;
use pocketmine\level\generator\populator\Ore;
use pocketmine\level\generator\populator\Populator;
use pocketmine\level\generator\populator\TallGrass;
use pocketmine\level\generator\populator\Tree;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\utils\Random;

class NotSoFlat extends Generator{

	/** @var Populator[] */
	private $populators = [];
	/** @var GenerationChunkManager */
	private $level;
	/** @var Random */
	private $random;
	private $worldHeight = 65;
	private $waterHeight = 63;
	/** @var Simplex */
	private $noiseHills;
	/** @var Simplex */
	private $noiseBase;

	private $cfg;
	private $preset;
	private $structure;	// terrain structure
	private $strata;
	private $waterBlock;

	public function __construct(array $options = []){
		$this->cfg = [];
		if (isset($options["preset"]) and $options["preset"] != "") {
			$this->preset = $options["preset"];
		} else {
			$this->preset = false;
		}
	}

	public function getName(){
		return "notsoflat";
	}
	public function getSettings(){
		return [ "preset" => $this->preset ];
	}

	public function parsePreset($preset){
		$PRESETS= [
			"overworld" => "nsfv1;7,59x1,3x3,2;1;". //0
			"spawn(radius=10 block=89),". 	// Glowstones
			"dsq(min=50 max=85 water=65 off=100),".
			"decoration(treecount=1,1 grasscount=5:0)",
			"plains" => "nsfv1;temperate;1;". 	//1
			'spawn(radius=10 block=48),'.	// Cobblestones...
			"dsq(min=50 max=70 strata=50:52:54:56:70 water=56 dotsz=0.6),".
			"decoration(treecount=1:1 grasscount=5:0 desertplant=0:0)",
			"ocean" => "nsfv1;temperate;0;".	//2
			'spawn(radius=10 block=24),'.	// sandstones
			"dsq(min=24 max=90 water=60 strata=30:54:60:74:80 dotsz=0.8),".
			"decoration(treecount=1:1 grasscount=5:0 desertplant=0:0)",
			"hills" => "nsfv1;temperate;1;".	//3
			'spawn(radius=10 block=48),'.	// Cobblestones...
			"dsq(min=24 max=90 water=30 strata=25:27:29:32:80 dotsz=0.7),".
			"decoration(treecount=1:1 grasscount=5:0 desertplant=0:0)",
			"mountains" => "nsfv1;temperate;1;".//4
			'spawn(radius=10 block=48),'.	// Cobblestones...
			"dsq(min=24 max=90 water=30 strata=25:27:29:32:55 dotsz=1.0 fn=exp fdat=2),".
			"decoration(treecount=1:1 grasscount=5:0 desertplant=0:0)",
			"flatland" => "nsfv1;7,59x1,3x3,12:7,59x1,3x3,2;1;".
			'spawn(radius=10 block=48),'.	// Cobblestones...
			"dsq(min=53 max=57 strata=55 water=55 dotsz=0.7),".
			"decoration(treecount=2:1 grasscount=5:5 desertplant=0:0)",
			"hell" => "nsfv1;hell;8;".		//6
			'spawn(radius=10 block=89),'.	// glowstone
			"dsq(min=40 max=80 water=55 waterblock=11 fn=exp fndat=1.7 hell=1)",
			"desert" => "nsfv1;arid;2;".	//7
			'spawn(radius=10 block=24),'.	// Cobblestones...
			"dsq(min=50 max=70 strata=1:2:52:65:68 water=51 dotsz=0.8),".
			"decoration(desertplant=5:1)",
			"mesa" => "nsfv1;temperate;1;".	//8
			'spawn(radius=10 block=48),'.	// Cobblestones...
			"dsq(min=24 max=90 water=30 strata=25:27:29:32:60 dotsz=0.9 fn=exp fdat=0.5),".
			"decoration(treecount=1:1 grasscount=5:0 desertplant=0:0)",
			"desert hills" => "nsfv1;arid;2;".	//9
			'spawn(radius=10 block=24),'.	// Sandstone
			"dsq(min=30 max=80 strata=1:2:32:60:68 water=32 dotsz=0.8 fn=exp fndat=1.5),".
			"decoration(desertplant=2:1)",
		];
		$STRATA=[
			"temperate" =>
			'7,59x1,3x3,2x13:'.	// Bottom ocean GRAVEL
			'7,59x1,5x3:'.	// Deep ocean DIRT
			'7,59x1,3x3,2x12:'.	// Shallow water SAND
			'7,59x1,5x3,2x24,3x12:'.	// Beach SAND
			'7,59x1,3x3,2,12:'.	// Plains GRASS
			'7,59x1,3x4,1',	// High mountains STONE
			"arid" =>
			'7,59x1,3x3,2x13:'.	// Bottom ocean GRAVEL
			'7,59x1,5x3:'.		// Deep ocean DIRT
			'7,59x1,3x3,2x12:'.	// Shallow water SAND
			'7,3x1,52x24,8x12:'.	// Ground SAND
			'7,30x1,30x24:'.		// Mountains SANDSTONE
			'7,30x1,30x13',		// Mountain peaks GRAVEL
			"hell" =>
			'7,2x87,10x11,20x87',	// Netherrack
		];

		if (!$preset) {
			$ids = array_keys($PRESETS);
			$preset = $ids[intval($this->random->nextFloat()*count($ids))];
			unset($ids);
		} else {
			$this->random->nextFloat();
		}
		//echo("[DEBUG] preset=$preset\n");
		if (isset($PRESETS[$preset])) $preset = $PRESETS[$preset];

		// Make sense of preset line...
		$preset = explode(";", $preset);
		$version = (int) $preset[0];
		$blocks = @$preset[1];
		if (isset($STRATA[$blocks])) $blocks = $STRATA[$blocks];
		$biome = isset($preset[2]) ? $preset[2]:1;
		$options = isset($preset[3]) ? $preset[3]:"";

		// Parse block structure
		$this->structure = array();
		$j = 0;
		foreach (explode(":",$blocks) as $blkset) {
			$matches = array();
			preg_match_all('#(([0-9]{0,})x?([0-9]{1,3}:?[0-9]{0,2})),?#',
								$blkset, $matches);
			$y = 0;
			$this->structure[$j] = array();

			foreach($matches[3] as $i => $b){
				$b = Item::fromString($b);
				$cnt = $matches[2][$i] === "" ? 1:intval($matches[2][$i]);
				for($cY = $y, $y += $cnt; $cY < $y; ++$cY){
					$this->structure[$j][$cY] = [$b->getID(),$b->getDamage()];
				}
			}
			++$j;
		}

		//////////////////////////////////////////////////////////////////////
		// additional options
		preg_match_all('#(([0-9a-z_]{1,})\(?([0-9a-z_ =:\.]{0,})\)?),?#', $options, $matches);
		foreach($matches[2] as $i => $option){
			$params = true;
			if($matches[3][$i] !== ""){
				$params = array();
				$p = explode(" ", $matches[3][$i]);
				foreach($p as $k){
					$k = explode("=", $k);
					if(isset($k[1])){
						$params[$k[0]] = $k[1];
					}
				}
			}
			$this->cfg[$option] = $params;
		}
	}

	public function init(ChunkManager $level, Random $random){
		$this->level = $level;
		$this->random = $random;
		$this->random->setSeed($this->level->getSeed());
		$this->noiseHills = new Simplex($this->random, 3, 0.1, 0.5, 12);
		$this->noiseBase = new Simplex($this->random, 16, 0.6, 1.0, 16);

		$this->parsePreset($this->preset);

		if (isset($this->cfg["decoration"])) {
			$ores = new Ore();
			$ores->setOreTypes([
				new OreType(new CoalOre(), 20, 16, 0, 128),
				new OreType(new IronOre(), 20, 8, 0, 64),
				new OreType(new RedstoneOre(), 8, 7, 0, 16),
				new OreType(new LapisOre(), 1, 6, 0, 32),
				new OreType(new GoldOre(), 2, 8, 0, 32),
				new OreType(new DiamondOre(), 1, 7, 0, 16),
				new OreType(new Dirt(), 20, 32, 0, 128),
				new OreType(new Gravel(), 10, 16, 0, 128),
			]);
			$this->populators[] = $ores;

			if (isset($this->cfg["decoration"]["treecount"])) {
				$tc = explode(":",$this->cfg["decoration"]["treecount"]);
				if (!isset($tc[0])) $tc[0] = 0;
				if (!isset($tc[1])) $tc[0] = 0;
				if ($tc[0] != 0 && $tc[1] != 0) {
					$trees = new Tree();
					$trees->setBaseAmount($tc[0]);
					$trees->setRandomAmount($tc[1]);
					$this->populators[] = $trees;
				}
			}

			if (isset($this->cfg["decoration"]["grasscount"])) {
				$tc = explode(":",$this->cfg["decoration"]["grasscount"]);
				if (!isset($tc[0])) $tc[0] = 0;
				if (!isset($tc[1])) $tc[0] = 0;
				if ($tc[0] != 0 && $tc[1] != 0) {
					$tallGrass = new TallGrass();
					$tallGrass->setBaseAmount($tc[0]);
					$tallGrass->setRandomAmount($tc[1]);
					$this->populators[] = $tallGrass;
				}
			}
			if (isset($this->cfg["decoration"]["desertplant"])) {
				$tc = explode(":",$this->cfg["decoration"]["desertplant"]);
				if (!isset($tc[0])) $tc[0] = 0;
				if (!isset($tc[1])) $tc[0] = 0;
				if ($tc[0] != 0 && $tc[1] != 0) {
					$cacti = new DesertPlant();
					$cacti->setBaseAmount($tc[0]);
					$cacti->setRandomAmount($tc[1]);
					$this->populators[] = $cacti;
				}
			}
		}
		if (isset($this->cfg["dsq"])) {
			$dsq = $this->cfg["dsq"];
		} else {
			$dsq = array();
		}
		// Define some suitable defaults
		$min = isset($dsq["min"]) ? intval($dsq["min"]) : 32;
		$max = isset($dsq["max"]) ? intval($dsq["max"]): 120;
		if ($min > $max) {
			list($min,$max) = array($max,$min);
		}

		//$this->waterLevel = isset($dsq["water"])?
		//intval($dsq["water"]):$min+($max-$min)/3;
		//$off = floatval(isset($dsq["off"]) ? intval($dsq["off"]) : 100);
		$this->waterBlock = isset($dsq["waterblock"]) ?
								intval($dsq["waterblock"]) : Block::STILL_WATER;
		//$this->dotsz = floatval(isset($dsq["dotsz"]) ? $dsq["dotsz"] : 0.9);
		//$this->dotoff= array('x'=>intval((1.0-$this->dotsz)*$this->rnd()
		//*self::DSQ_SIZE),
		//'y'=>intval((1.0-$this->dotsz)*$this->rnd()
		//*self::DSQ_SIZE));
		//$fn = isset($dsq["fn"]) ? $dsq["fn"] : "linear";
		//$fndat = explode(":",isset($dsq["fdat"]) ? $dsq["fdat"] : 1);

		// Strata map...
		$this->strata = array();
		if (isset($dsq["strata"])) {
			$strata = explode(":",$dsq["strata"]);
		} else {
			$strata = array();
		}
		$i = 0;
		for ($y=0;$y < 128 ; ++$y) {
			if (isset($strata[$i])) {
				if ($strata[$i] > $y) {
					$this->strata[$y] = $i;
				} else {
					$this->strata[$y] = ++$i;
				}
			} else {
				$this->strata[$y] = $i;
			}
		}
	}

	public function pickBlock($x,$y,$z,$h) {
		if (isset($this->options["dsq"]["hell"])) {
			if ($y > 126) return [ Block::BEDROCK, 0];
			if ($y > 125) return [ Block::NETHERRACK, 0];
		}

		if ($y > $h) {
			// This is above ground
			if ($y < $this->waterHeight) {
				return [$this->waterBlock, 0];
			} else {
				return [ Block::AIR, 0];
			}
		}

		if (!isset($this->structure[$this->strata[$h]])) return DIRT;
		$structure = $this->structure[$this->strata[$h]];

		$floorLevel = count($structure);

		$b = intval($y * ($floorLevel-1) / $h);
		$q = $b;
		if ($b > $floorLevel) $b = $floorLevel - 1;
		return $structure[$b];
	}


	public function generateChunk($chunkX, $chunkZ){
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());
		$hills = [];
		$base = [];
		$incline = [];
		for($z = 0; $z < 16; ++$z){
			for($x = 0; $x < 16; ++$x){
				$i = ($z << 4) + $x;
				$hills[$i] = $this->noiseHills->noise2D($x + ($chunkX << 4), $z + ($chunkZ << 4), true);
				$base[$i] = $this->noiseBase->noise2D($x + ($chunkX << 4), $z + ($chunkZ << 4), true);
				if($base[$i] < 0){
					$base[$i] *= 0.5;
				}
			}
		}

		$chunk = $this->level->getChunk($chunkX, $chunkZ);

		for($z = 0; $z < 16; ++$z){
			for($x = 0; $x < 16; ++$x){
				$i = ($z << 4) + $x;
				$height = $this->worldHeight + $hills[$i] * 14 + $base[$i] * 7;
				$height = (int) $height;

				for($y = 0; $y < 128; ++$y){
					$block = $this->pickBlock($x,$y,$z,$height);
					$chunk->setBlock($x,$y,$z,...$block);
				}
			}
		}

	}

	public function populateChunk($chunkX, $chunkZ){
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());
		foreach($this->populators as $populator){
			$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());
			$populator->populate($this->level, $chunkX, $chunkZ, $this->random);
		}
	}

	public function getSpawn(){
		return $this->level->getSafeSpawn(new Vector3(127.5, 128, 127.5));
	}

}
