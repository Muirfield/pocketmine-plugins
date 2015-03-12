<?php
namespace ImportMap;
use \pocketmine\scheduler\AsyncTask;
use \pocketmine\Server;

use pmimporter\LevelFormatManager;
use pmimporter\anvil\Anvil;
use pmimporter\mcregion\McRegion;
use pmimporter\Copier;
use pmimporter\Blocks;
use pmimporter\ImporterException;
use pmimporter\Plugin;

class Importer extends AsyncTask {
  private $srcpath;
  private $dstpath;
  private $fmt;

  public static function blockName($str) {
    if (is_numeric($str)) return intval($str);
    return Blocks::getBlockByName($str);
  }

  public static function init(array &$btab) {
    Blocks::__init();
    // Enable block xlate rules...
    foreach ($btab as $a=>$b) {
      $a = self::blockName($a);
      $b = self::blockName($b);
      Blocks::addRule($a,$b);
    }
  }
  public function __construct($srcpath,$dstpath,$fmt) {
    $this->srcpath = $srcpath;
    $this->dstpath = $dstpath;
    $this->fmt = $fmt;
  }
  public function onRun() {
    try {
      $start = time();
      LevelFormatManager::addFormat(Anvil::class);
      LevelFormatManager::addFormat(McRegion::class);

      $srcpath = $this->srcpath;
      $dstpath = $this->dstpath;
      $world = basename($dstpath);

      $srcformat = LevelFormatManager::getFormat($srcpath);
      $dstformat = LevelFormatManager::getFormatByName($this->fmt);
      if (!$dstformat) {
	$this->setResult("Unsupported output format");
	return;
      }

      $srcfmt = new $srcformat($srcpath);
      $regions = $srcfmt->getRegions();
      if (!count($regions)) {
	$this->setResult("No regions found in $srcpath");
	return;
      }

      $dstformat::generate($dstpath,$world,
			   $srcfmt->getSpawn(),
			   $srcfmt->getSeed(),
			   $srcfmt->getGenerator(),
			   $srcfmt->getGeneratorOptions());

      $dstfmt = new $dstformat($dstpath);

      foreach ($regions as $region) {
	Copier::copyRegion($region,$srcfmt,$dstfmt,null);
      }
      $end = time() - $start;
      $this->setResult("Imported world in $end seconds");
    } catch (ImporterException $e) {
      $this->setResult("ImportMap error: ".$e->getMessage());
    }
  }
  public function onCompletion(Server $server) {
    $server->broadcastMessage($this->getResult());
  }
}