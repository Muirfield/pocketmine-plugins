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

function analyze_chunk(Chunk $chunk) {
  foreach ($chunk->getEntities() as $entity) {
    if (!isset($entity->id)) continue;
    echo("//// ENTITY ////\n");
    print_r($entity);
  }
  foreach ($chunk->getTileEntities() as $tile) {
    if (!isset($tile->id)) continue;
    echo("//// TILE_ENTITY ////\n");
    print_r($tile);
  }
}


$regions = $fmt->getRegions();


if (isset($argv[0]) && $argv[0] == '--all') {
  // Process all regions
  $argv = array_keys($regions);
  echo "Analyzing ".count($regions)." regions\n";
}

if (count($argv) == 0)
  die("Must specify --all for all regions, or the specific regions\n");

foreach ($argv as $ppx) {
  $ppx = explode(':',$ppx,2);
  $pp = array_shift($ppx);

  if (!isset($regions[$pp])) die("Region $pp does not exist\n");
  echo " Reg: $pp\n";
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
      $cX = $rX*32+$oX;
      for ($oZ=0;$oZ < 32; $oZ++) {
	$cZ = $rZ*32+$oZ;
	if ($region->chunkExists($oX,$oZ)) {
	  ++$chunks;
	  $chunk = $region->readChunk($oX,$oZ);
	  if ($chunk) analyze_chunk($chunk,$stats);
	}
      }
    }
  }
}
