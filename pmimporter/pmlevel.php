<?php
require_once(dirname(realpath(__FILE__)).'/classlib/autoload.php');

use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\String;
use pocketmine\nbt\tag\Long;
use pocketmine\nbt\tag\Compound;

define('CMD',array_shift($argv));
$wpath=array_shift($argv);
if (!isset($wpath)) die("No path specified\n");
if (!is_dir($wpath)) die("$wpath: does not exist\n");
$lvfile = $wpath."/level.dat";
if (!is_file($lvfile)) die("$wpath: Missing level.dat\n");

$nbt = new NBT(NBT::BIG_ENDIAN);
$nbt->readCompressed(file_get_contents($lvfile));
$levelData = $nbt->getData()->Data;
//print_r($levelData);
$changed = 0;
foreach ($argv as $kv) {
  $kv = explode("=",$kv,2);
  if (count($kv) != 2) die("Invalid element: $kv[0]\n");
  list($k,$v) = $kv;
  switch ($k) {
  case "spawn":
    $pos = explode(",",$v);
    if (count($pos)!=3) die("Invalid spawn location: ".implode(",",$pos).NL);
    list($x,$y,$z) = $pos;
    if (($x=intval($x)) != $levelData->SpawnX) {
      ++$changed;
      $levelData->SpawnX = new Int("SpawnX", (int) $x);
    }
    if (($y=intval($y)) != $levelData->SpawnY) {
      ++$changed;
      $levelData->SpawnY = new Int("SpawnY", (int) $y);
    }
    if (($z=intval($z)) != $levelData->SpawnZ) {
      ++$changed;
      $levelData->SpawnZ = new Int("SpawnZ", (int) $z);
    }
    break;
    break;
  case "name":
    // LevelName : string
    if ($levelData->LevelName != $v) {
      ++$changed;
      $levelData->LevelName = new String("LevelName",$v);
    }
    break;
  case "seed": //RandomSeed : Long
    $v = intval($v);
    if ($levelData->RandomSeed != $v) {
      ++$changed;
      $levelData->RandomSeed = new Long("RandomSeed",$v);
    }
    break;
  case "generator": // generatorName(String),generatorVersion(Int)
    $v = explode(",",$v);
    if (count($v) < 1 || count($v) > 2) die("Invalid generator: $kv[0]\n");
    $genName = array_shift($v);
    if ($levelData->generatorName != $genName) {
      ++$changed;
      $levelData->generatorName = new String("generatorName",$genName);
    }
    if (count($v)) {
      $genVersion = intval(array_shift($v));
      if ($levelData->generatorVersion != $genVersion) {
	++$changed;
	$levelData->generatorVersion = new Int("generatorVersion",(int)$genVersion);
      }
    }
    break;
  case "generatorOptions": // generatorOptions(String)
    if ($levelData->generatorOptions != $v) {
      ++$changed;
      $levelData->generatorOptions = new String("generatorOptions",$v);
    }
    break;
  default:
    die("Attribute: $k not supported\n");
  }
}
if ($changed) {
  $nbt = new NBT(NBT::BIG_ENDIAN);
  $nbt->setData(new Compound(null, ["Data" => $levelData]));
  file_put_contents($lvfile,$nbt->writeCompressed());
}

echo "World:     $wpath\n";
//echo "version:   ".$levelData["version"].NL;
echo "Spawn:     ".implode(",",[$levelData->SpawnX,$levelData->SpawnY,$levelData->SpawnZ]).NL;
echo "Name:      ".$levelData["LevelName"].NL;
echo "Seed:      ".$levelData["RandomSeed"].NL;
echo "Generator: ".$levelData["generatorName"]." v".$levelData["generatorVersion"].NL;
echo "GenPreset: ".$levelData["generatorOptions"].NL;
