<?php
if (!defined('CLASSLIB_DIR'))
  require_once(dirname(realpath(__FILE__)).'/../classlib/autoload.php');

use pmimporter\LevelFormatManager;
use pmimporter\anvil\Anvil;
use pmimporter\mcregion\McRegion;
use pmimporter\mcpe020\McPe020;
use pmimporter\pm13\Pm13;
use pmimporter\Chunk;
use pmimporter\Blocks;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Int;


LevelFormatManager::addFormat(Anvil::class);
LevelFormatManager::addFormat(McRegion::class);
LevelFormatManager::addFormat(McPe020::class);
LevelFormatManager::addFormat(Pm13::class);

define('CMD',array_shift($argv));
$wpath=array_shift($argv);
if (!isset($wpath)) die("No world path specified\n");
$wpath = preg_replace('/\/*$/',"",$wpath).'/';
if (!is_dir($wpath)) die("$wpath: not found\n");

$provider = LevelFormatManager::getFormat($wpath);
if (!$provider) die("$wpath: Format not recognized\n");

$fmt = new $provider($wpath,true);
echo "Path:      ".$fmt->getPath().NL;
echo "Name:      ".$fmt->getName().NL;
echo "Format:    ".$fmt::getFormatName().NL;
echo "Seed:      ".$fmt->getSeed().NL;
echo "Generator: ".$fmt->getGenerator().NL;
$opts = $fmt->getGeneratorOptions();
if (isset($opts["preset"])) echo "GenOpts:   ".$opts["preset"].NL;
$spawn = $fmt->getSpawn();
echo "Spawn:     ".implode(',',[$spawn->getX(),$spawn->getY(),$spawn->getZ()]).NL;

echo "Regions:";
$regions = $fmt->getRegions();
foreach ($regions as $pp) {
  list($rX,$rZ) = $pp;
  echo " $rX,$rZ";
}
echo "\n";

function incr(&$stats,$attr) {
  if (isset($stats[$attr])) {
    ++$stats[$attr];
  } else {
    $stats[$attr] = 1;
  }
}

function analyze_chunk(Chunk $chunk,&$stats) {
  if ($chunk->isPopulated()) incr($stats,"-populated");
  if ($chunk->isGenerated()) incr($stats,"-generated");

  for ($x = 0;$x < 16;$x++) {
    for ($z=0;$z < 16;$z++) {
      for ($y=0;$y < 128;$y++) {
	list($id,$meta) = $chunk->getBlock($x,$y,$z);
	incr($stats,$id);
      }
      $height = $chunk->getHeightMap($x,$z);
      if (!isset($stats["Height:Max"])) {
	$stats["Height:Max"] = $height;
      } elseif ($height > $stats["Height:Max"]) {
	$stats["Height:Max"] = $height;
      }
      if (!isset($stats["Height:Min"])) {
	$stats["Height:Min"] = $height;
      } elseif ($height < $stats["Height:Min"]) {
	$stats["Height:Min"] = $height;
      }
      if (!isset($stats["Height:Sum"])) {
	$stats["Height:Sum"] = $height;
      } else {
	$stats["Height:Sum"] += $height;
      }
      incr($stats,"Height:Count");
    }
  }
  foreach ($chunk->getEntities() as $entity) {
    if (!isset($entity->id)) continue;
    incr($stats,"ENTITY:".$entity->id->getValue());
    //print_r($entity);
  }
  foreach ($chunk->getTileEntities() as $tile) {
    if (!isset($tile->id)) continue;
    incr($stats,"TILE:".$tile->id->getValue());
  }
}

if (isset($argv[0]) && $argv[0] == '--all') {
  // Process all regions
  $argv = array_keys($regions);
  echo "Analyzing ".count($regions)." regions\n";
}

foreach ($argv as $ppx) {
  $ppx = explode(':',$ppx,2);
  $pp = array_shift($ppx);

  if (!isset($regions[$pp])) die("Region $pp does not exist\n");
  echo " Reg: $pp ";
  $chunks = 0;
  list($rX,$rZ) = $regions[$pp];
  $region = $fmt->getRegion($rX,$rZ);
  $chunks = 0;
  $stats = [];

  if (count($ppx)) {
    foreach (explode('+',$ppx[0]) as $cp) {
      $cp = explode(',',$cp);
      if (count($cp) != 2) die("Invalid chunk ids: ".$ppx[0].NL);
      list($oX,$oZ) = $cp;
      if (!is_numeric($oX) || !is_numeric($oZ)) die("Not numeric $oX,$oZ\n");
      if ($oX < 0 || $oZ < 0 || $oX >= 32 || $oZ >= 32)
	die("Invalid location $oX,$oZ\n");
      if ($region->chunkExists($oX,$oZ)) {
	++$chunks;
	$chunk = $region->readChunk($oX,$oZ);
	if ($chunk)
	  analyze_chunk($chunk,$stats);
	else
	  echo "Unable to read chunk: $oX,$oZ\n";
      }
    }
  } else {
    for ($oX=0;$oX < 32;$oX++) {
      //for ($oX=7;$oX < 8;$oX++) {
      $cX = $rX*32+$oX;
      for ($oZ=0;$oZ < 32; $oZ++) {
	if (!($oZ % 16)) echo ".";
	//for ($oZ=6;$oZ < 9; $oZ++) {
	$cZ = $rZ*32+$oZ;
	if ($region->chunkExists($oX,$oZ)) {
	  ++$chunks;
	  $chunk = $region->readChunk($oX,$oZ);
	  if ($chunk) analyze_chunk($chunk,$stats);
	}
      }
    }
  }
  echo "\n";
  unset($region);
  echo "  Chunks:\t$chunks\n";
  if (isset($stats["Height:Count"]) && isset($stats["Height:Sum"])) {
    $stats["Height:Avg"] = $stats["Height:Sum"]/$stats["Height:Count"];
    unset($stats["Height:Count"]);
    unset($stats["Height:Sum"]);
  }
  $sorted = array_keys($stats);
  natsort($sorted);
  foreach ($sorted as $k) {
    if (is_numeric($k)) {
      $v = Blocks::getBlockById($k);
      $v = $v !== null ? "$v ($k)" : "*Unsupported* ($k)";
    } else {
      $v = $k;
    }
    echo "  $v:\t".$stats[$k].NL;
  }
}
