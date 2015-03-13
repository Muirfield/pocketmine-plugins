<?php
if (!defined('CLASSLIB_DIR'))
  require_once(dirname(realpath(__FILE__)).'/../classlib/autoload.php');

use pmimporter\LevelFormatManager;
use pmimporter\anvil\Anvil;
use pmimporter\mcregion\McRegion;
use pmimporter\Chunk;
use pmimporter\Blocks;


LevelFormatManager::addFormat(Anvil::class);
LevelFormatManager::addFormat(McRegion::class);

define('CMD',array_shift($argv));
$wpath=array_shift($argv);
if (!isset($wpath)) die("No world path specified\n");
$wpath = preg_replace('/\/*$/',"",$wpath).'/';
if (!is_dir($wpath)) die("$wpath: not found\n");

$provider = LevelFormatManager::getFormat($wpath);
if (!$provider) die("$wpath: Format not recognized\n");

$fmt = new $provider($wpath);
$regions = $fmt->getRegions();


foreach ($argv as $ppx) {
  $ppx = explode(':',$ppx,2);
  $pp = array_shift($ppx);
  if (!isset($regions[$pp])) die("Region $pp does not exist\n");

  if (!count($ppx)) die("Must specify the chunk to dump\n");

  list($rX,$rZ) = $regions[$pp];
  $region = $fmt->getRegion($rX,$rZ);

  foreach (explode('+',$ppx[0]) as $cp) {
    $cp = explode(',',$cp);
    if (count($cp) != 2) die("Invalid chunk ids: ".$ppx[0].NL);
    list($oX,$oZ) = $cp;
    if (!is_numeric($oX) || !is_numeric($oZ)) die("Not numeric $oX,$oZ\n");
    if ($oX < 0 || $oZ < 0 || $oX >= 32 || $oZ >= 32)
      die("Invalid location $oX,$oZ\n");
    if (!$region->chunkExists($oX,$oZ)) {
      echo("chunk($oX,$oZ) does not exist!\n");
      continue;
    }
    $chunkData = $region->readChunkData($oX,$oZ);
    echo $chunkData;
  }
}
