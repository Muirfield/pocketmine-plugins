<?php
namespace ImportMap;
use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\Config;

//////////////////////////////////////////////////////////////////////
use pocketmine\level\Level;
use pocketmine\level\format\LevelProviderManager;
use pocketmine\level\generator\Generator;
use pocketmine\level\format\anvil\Anvil;
use pocketmine\level\format\leveldb\LevelDB;
use pocketmine\level\format\mcregion\McRegion;

//////////////////////////////////////////////////////////////////////
//use pocketmine\Server;
//use pocketmine\permission\Permission;
//use pocketmine\math\Vector3;
//use pocketmine\level\format\mcregion\RegionLoader;

class Main extends Plugin implements CommandExecutor {

  private function info($msg) {
    $this->getLogger()->info($msg);
  }
  public function onEnable() {
    @mkdir($this->getDataFolder());
    $btab = [ 25 => 0,	// Note block
	      28 => 27,	// Detector Rail
	      29 => 0,	// Sticky Piston
	      33 => 0,	// Piston
	      34 => 0,	// Piston Head
	      36 => 0,	// Piston Extension
	      69 => 0,	// Lever
	      70 => 44,	// Stone Pressure Plate
	      72 => 158,	// Wooden Pressure Plate
	      75 => 50,	// Inactive redstone torch
	      76 => 50,	// Active redstone torch
	      77 => 0,	// Stone Button
	      84 => 5,	// Jukebox
	      88 => 13,	// Soul Sand
	      90 => 247,	// Nether Portal
	      93 => 171,	// Unpowered repeater
	      94 => 171,	// Powered repeater
	      97 => 1,	// Monster Egg
	      113 => 139,	// Nether Brick Fence
	      115 => 31,	// Nether Wart
	      116 => 58,	// Enchanting Table
	      117 => 20,	// Brewing Stand
	      118 => 61,	// Cauldron
	      119 => 247,	// End Portal
	      122 => 173,	// Dragon Egg
	      123 => 246,	// Redstone Lamp
	      124 => 89,	// Lit Redstone Lamp
	      125 => 157,	// Double Wooden Slab
	      126 => 158,	// Wooden Slab
	      130 => 54,	// Ender Chest
	      131 => 0,	// Tripwire Hook
	      132 => 0,	// Tripwire
	      137 => 0,	// Command Block
	      138 => 89,	// Beacon
	      140 => 2,	// Flower Pot
	      143 => 0,	// Wooden Button
	      144 => 91, // Skull
	      145 => 49,	// anvil
	      146 => 54,	// Trapped chest
	      147 => 171, // Light pressure plate
	      148 => 171,	// Heavy preassure plate
	      149 => 0,	// Unpowered comparator
	      150 => 0,	// Powered comparator
	      151 => 0,	// Daylight detector
	      152 => 74,	// Redstone block
	      153 => 155,	// Nether Quartz Ore
	      154 => 0,	// Hopper
	      160 => 102,	// Stained Glass Pane
	      161 => 18,	// Acacia/Dark Oak Leaves
	      162 => 17,	// Acacia/Dark Oak wood
	      165 => 243,	// Slime block
	      166 => 7,	// Barrier
	      167 => 96,	// Iron Trapdoor
	      168 => 20,	// Prismarine
	      169 => 89,	// Sea lantern
	      175 => 38,	// Large Flowers
	      176 => 63,	// Standing banner
	      177 => 68,	// Wall banner
	      178 => 0,	// Inverted Light Sensor
	      179 => 24,	// Red Sandstone
	      180 => 128,	// Red Sandstone stairs
	      181 => 24,	// Double Red Sanstone slab
	      182 => 44,	// Red Sandstone slab
	      ];
    for ($x = 198;$x <= 242;$x++) $btab[$x] = 0;
    $this->xtab = (new Config($this->getDataFolder()."config.yml",Config::YAML,$btab))->getall();
    $this->info("ImportMap Loaded!");
    $this->info("xtab: ".count($this->xtab)." entries");
  }
  public function onDisable() {
    $this->info("ImportMap Unloaded!");
  }
  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
    switch($cmd->getName()) {
    case "im":
      if (!$sender->hasPermission("im.cmd.im")) {
	$sender->sendMessage("You do not have permission to do that.");
	return true;
      }
      if (isset($args[0]) && isset($args[1])) {
	$this->imImport($sender,$args[0],$args[1]);
      } else {
	$sender->sendMessage("Usage: im <path> <world>");
      }
      break;
    default:
      return false;
    }
    return true;
  }
  public function imImport(CommandSender $c,$impath,$world) {
    if ($this->getServer()->isLevelGenerated($world)) {
      $c->sendMessage("$world already exists");
      return;
    }
    $impath = preg_replace('/\/*$/',"",$impath).'/';
    if (!is_dir($impath)) {
      $c->sendMessage("$impath not found");
      return;
    }

    $srcprovider = LevelProviderManager::getProvider($impath);
    if (!$srcprovider) {
      $c->sendMessage("$impath: Format not recognized");
      return;
    }
    $c->sendMessage("Importing: $impath ($srcprovider)");
    $srclevel = new Level($this->getServer(),basename($impath),$impath,$srcprovider);
    $srcprovider = $srclevel->getProvider();

    $dstprovider = $this->getServer()->getProperty("level-settings.default-format","mcregion");
    $dstprovider = LevelProviderManager::getProviderByName($dstprovider);
    if ($dstprovider === null) {
      $dstprovider = LevelProviderManager::getProviderByName("mcregion");
    }
    $dstpath = $this->getServer()->getDataPath()."worlds/".$world."/";
    $generator = Generator::getGenerator($srcprovider->getGenerator());

    $c->sendMessage("Generator: ".$generator);
    $c->sendMessage("-        : ".Generator::getGeneratorName($generator));

    $dstprovider::generate($dstpath,$world,
			   $srcprovider->getSeed(),$generator,
			   $srcprovider->getGeneratorOptions());
    $dstlevel = new Level($this->getServer(),$world,$dstpath,$dstprovider);
    $dstprovider = $dstlevel->getProvider();

    $dstprovider->setSpawn($srcprovider->getSpawn());
    $dstprovider->saveLevelData();

    if ($srcprovider instanceof Anvil) {
      $this->imCopyRegionChunks($c,$srcprovider,$dstprovider);
    } elseif ($srcprovider instanceof McRegion) {
      $this->imCopyRegionChunks($c,$srcprovider,$dstprovider);
    } else {
      $c->sendMessage("Unsupported LevelProvider ".get_class($srcprovider));
    }
  }
  private function imMapLoader($provider) {
    if ($provider instanceof Anvil) {
      $ext = "mca";
      $loader="anvil";
    } elseif ($provider instanceof McRegion) {
      $ext = "mcr";
      $loader="mcregion";
    } else {
      return [null,null];
    }
    $loader = "pocketmine\\level\\format\\$loader\\RegionLoader";
    return [$loader,$ext];
  }

  private function imCopyRegionChunks(CommandSender $c,$srcprovider,$dstprovider) {
    list($srcloader,$ext) = $this->imMapLoader($srcprovider);
    if ($ext === null) {
      $c->sendMessage("Internal error!");
      return;
    }
    list($dstloader,) = $this->imMapLoader($dstprovider);

    /*$spawn = $srcprovider->getSpawn();
    $rX = $spawn->getX() >> 9;
    $rZ = $spawn->getZ() >> 9;
    $srcregion = new $srcloader($srcprovider,$rX,$rZ);
    $dstregion = new $dstloader($dstprovider,$rX,$rZ);

    $this->imCopyRegion($c,$srcregion,$dstregion,$rX,$rZ);
    */
    $path = $srcprovider->getPath();
    $files = glob($path."/region/r*.$ext");

    foreach ($files as $f) {
      $pp = [];
      if (preg_match('/r\.(-?\d+)\.(-?\d+)\.'.$ext.'$/',$f,$pp)) {
	array_shift($pp);

	list($rX,$rZ) = $pp;
	$srcregion = new $srcloader($srcprovider,$rX,$rZ);
	$dstregion = new $dstloader($dstprovider,$rX,$rZ);

	$this->imCopyRegion($c,$srcregion,$dstregion,$rX,$rZ);
	unset($srcregion,$dstregion);
      }
    }
  }
  private function imCopyRegion(CommandSender $c,$srcregion,$dstregion,$rX,$rZ) {
    list($chunks,$copied,$conv) = [0,0,0];
    $stats = [];

    $c->sendMessage("Region: $rX,$rZ");

    for ($oX = 0; $oX < 32; $oX++) {
      $cX = $rX * 32 + $oX;
      for ($oZ = 0; $oZ < 32 ; $oZ++) {
	$cZ = $rZ * 32 + $oZ;
	if ($srcregion->chunkExists($oX,$oZ)) {
	  ++$chunks;
	  $srcchunk = $srcregion->readChunk($oX,$oZ,false,true);
	  if ($srcchunk->isPopulated() || $srcchunk->isGenerated()) {
	    ++$copied;
	    $dstchunk = $dstregion->readChunk($oX,$oZ,true,false);
	    $conv += $this->imCopyChunk($c,$srcchunk,$dstchunk,$stats);
	    $dstregion->writeChunk($dstchunk);
	  }
	}
      }
    }
    $dstregion->close();
    $srcregion->close();

    $c->sendMessage("Chunks:    $chunks");
    $c->sendMessage("Copied:    $copied");
    $c->sendMessage("Converted: $conv");
    $c->sendMessage("STATS:");
    $keys = array_keys($stats);
    sort($keys,SORT_NUMERIC);
    foreach ($keys as $bid) {
      if (isset($this->xtab[$bid])) {
	$c->sendMessage("$bid:\t".$stats[$bid]." (=> ".$this->xtab[$bid].")");
      } else {
	$c->sendMessage("$bid:\t".$stats[$bid]);
      }
    }
  }

  private function imCopyChunk(CommandSender $c,$srcchunk,$dstchunk,array &$stats) {
    $dstchunk->setPopulated($srcchunk->isPopulated());
    $dstchunk->setGenerated($srcchunk->isGenerated());
    $conv = 0;

    // Copy blocks...
    for ($x = 0;$x < 16;$x++) {
      for ($z=0;$z < 16;$z++) {
	for ($y=0;$y < 128;$y++) {
	  $b = $srcchunk->getBlockId($x,$y,$z);
	  if (!isset($stats[$b])) {
	    $stats[$b] = 1;
	  } else {
	    ++$stats[$b];
	  }
	  $d = $srcchunk->getBlockData($x,$y,$z);
	  if (isset($this->xtab[$b])) {
	    $b = $this->xtab[$b];
	    ++$conv;
	  }
	  $dstchunk->setBlock($x,$y,$z,$b,$d);
	  $dstchunk->setBlockSkyLight($x,$y,$z,$srcchunk->getBlockSkyLight($x,$y,$z));
	  $dstchunk->setBlockLight($x,$y,$z,$srcchunk->getBlockLight($x,$y,$z));
	}
	$dstchunk->setBiomeId($x,$z,$srcchunk->getBiomeId($x,$z));
      }
    }
    // Copy Arrays...

    $heights = $srcchunk->getHeightMapArray();
    foreach ($heights as $off => $y) {
      $x = $off & 0xf;
      $z = $off >> 4;
      $dstchunk->setHeightMap($x,$z,$y);
    }

    $colors = $srcchunk->getBiomeColorArray();
    foreach ($colors as $off => $color) {
      $x = $off & 0xf;
      $z = $off >> 4;
      $color = $color & 0xFFFFFF;
      list($r,$g,$b) = [$color >> 16, ($color >> 8) & 0xFF, $color & 0xFF];
      $dstchunk->setBiomeColor($x,$z,$r,$g,$b);
    }
    return $conv;
  }
}
