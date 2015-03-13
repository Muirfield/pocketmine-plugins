<?php
if (!defined('CLASSLIB_DIR'))
  require_once(dirname(realpath(__FILE__)).'/../classlib/autoload.php');

use pmimporter\LevelFormatManager;
use pmimporter\anvil\Anvil;
use pmimporter\mcregion\McRegion;
use pmimporter\Copier;
use pmimporter\Blocks;

LevelFormatManager::addFormat(Anvil::class);
LevelFormatManager::addFormat(McRegion::class);

define('CMD',array_shift($argv));
$dstformat = "mcregion";
$threads = 1;

while (count($argv)) {
  if ($argv[0] == "-f") {
    array_shift($argv);
    $dstformat = array_shift($argv);
    if (!isset($dstformat)) die("No format specified\n");
  } elseif ($argv[0] == "-c") {
    array_shift($argv);
    $rules = array_shift($argv);
    if (!isset($rules)) die("No rules file specified\n");
    loadRules($rules);
  } elseif ($argv[0] == "-t") {
    array_shift($argv);
    $threads = array_shift($argv);
    if (!isset($threads)) die("No value specified for -t\n");
    $threads = intval($threads);
    if ($threads < 1) die("Invalid thread value $threads\n");
  } else {
    break;
  }
}

if (!extension_loaded("pcntl")) $threads = 1;

$srcpath=array_shift($argv);
if (!isset($srcpath)) die("No src path specified\n");
$srcpath = preg_replace('/\/*$/',"",$srcpath).'/';
if (!is_dir($srcpath)) die("$srcpath: not found\n");
$dstpath=array_shift($argv);
if (!isset($dstpath)) die("No dst path specified\n");
$dstpath = preg_replace('/\/*$/',"",$dstpath).'/';
if (file_exists($dstpath)) die("$dstpath: already exist\n");

$srcformat = LevelFormatManager::getFormat($srcpath);
if (!$srcformat) die("$srcpath: Format not recognized\n");

$dstformat = LevelFormatManager::getFormatByName($dstformat);
if (!$dstformat) die("Output format not recognized\n");
//if (!is_a($dstformat,Anvil::class,true) && !is_a($dstformat,McRegion::class,true))
//  die("$dstformat: Format not supported\n");
if ($dstformat !== McRegion::class) die("$dstformat: Format not supported\n");

$srcfmt = new $srcformat($srcpath);
$regions = $srcfmt->getRegions();
if (!count($regions)) die("No regions found in $srcpath\n");

$dstformat::generate($dstpath,basename($dstpath),
		     $srcfmt->getSpawn(),
		     $srcfmt->getSeed(),
		     $srcfmt->getGenerator(),
		     $srcfmt->getGeneratorOptions());

$dstfmt = new $dstformat($dstpath,false);

//////////////////////////////////////////////////////////////////////
function loadRules($file) {
  $fp = fopen($file,"r");
  if ($fp === false) die("$file: Unable to open file\n");
  $state = 'blocks';
  while (($ln = fgets($fp)) !== false) {
    $ln = preg_replace('/^\s+/','',$ln);
    $ln = preg_replace('/\s+$/','',$ln);
    if ($ln == '') continue;
    if (preg_match('/^[;#]/',$ln)) continue;
    if (strtolower($ln) == 'blocks') {
      $state = 'blocks';
    } elseif (strtolower($ln) == 'tiles') {
      die("Unsupported ruleset: tiles\n");
    } elseif (strtolower($ln) == 'entities') {
      die("Unsupported ruleset: entities\n");
    } else {
      if ($state == 'blocks') {
	$pp = preg_split('/(\s|=)+/',$ln);
	if (count($pp) == 1) {
	  echo("Error parsing line: $ln[0]\n");
	  continue;
	} else {
	  for ($i=0;$i<2;++$i) {
	    if (is_numeric($pp[$i])) continue;
	    $pp[$i] = Blocks::getBlockByName($pp[$i]);
	    if ($pp[$i] === null) {
	      echo("Unknown block type: $ln\n");
	      continue;
	    }
	  }
	  Blocks::addRule($pp[0],$pp[1]);
	}
      } else {
	die("Invalid internal state: $state\n");
      }
    }
  }
  fclose($fp);
}

function pmconvert_status($state,$data) {
  switch ($state) {
  case "CopyRegionStart":
    echo "  Reg: $data\n";
    break;
  case "CopyChunk":
    echo ".";
    break;
  case "CopyRegionDone":
    echo "\n";
    break;
  default:
    echo ".";
  }
}


//////////////////////////////////////////////////////////////////////
function copyNextRegion() {
  global $regions,$workers;
  global $srcfmt,$dstfmt;

  if (!count($regions)) return;
  $region = array_pop($regions);
  $pid = pcntl_fork();

  if ($pid == 0) {
    echo "spawned: ".getmypid().NL;
    Copier::copyRegion($region,$srcfmt,$dstfmt,
		       __NAMESPACE__."\\pmconvert_status");;
    exit(0);
  } elseif ($pid == -1) {
    die("Could not fork\n");
  } else {
    $workers[$pid] = $region;
  }
}
if ($threads == 1) {
  foreach ($regions as $region) {
    Copier::copyRegion($region,$srcfmt,$dstfmt,
		       __NAMESPACE__."\\pmconvert_status");;
  }
} else {
  echo "Threads: $threads\n";
  $workers = [];
  for ($c = $threads;$c--;) {
    copyNextRegion();
  }
  while ($pid = pcntl_wait($rstatus)) {
    if (!isset($workers[$pid])) continue;
    list($rX,$rZ) = $workers[$pid];
    unset($workers[$pid]);
    if (pcntl_wexitstatus($rstatus)) {
      echo "$pid ($rX,$rZ) failled\n";
    } else {
      echo "$pid ($rX,$rZ) succesful\n";
    }
    if (count($regions)) {
      copyNextRegion();
    } else {
      if (!count($workers)) break;
    }
  }
  echo "ALL DONE\n";
}
