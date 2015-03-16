<?php
namespace pmimporter\pm13;
use pmimporter\LevelFormat;
use pmimporter\ImporterException;

use pocketmine_1_3\pmf\PMFLevel;

use pocketmine\math\Vector3;

class Pm13 implements LevelFormat {
  protected $path;
  protected $pmfLevel;

  public function __construct($path,$ro=true) {
    if (!$ro) {
      throw new ImporterException("$path: old skool format only supported read-only\n");
    }
    $path = preg_replace('/\/*$/',"",$path).'/';
    $this->path = $path;

    $pmfLevel = new PMFLevel($this->getPath()."level.pmf");
    $this->pmfLevel = $pmfLevel;
  }
  public function getPath() {
    return $this->path;
  }

  public static function generate($path, $name, Vector3 $spawn, $seed, $generator, array $options = []) {
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
  public function getName() {
    return $this->pmfLevel->getAttr("name");
  }
  public function getSeed() {
    return $this->pmfLevel->getAttr("seed");
  }
  public function getSpawn() {
    return new Vector3((float)$this->pmfLevel->getAttr("spawnX"),(float)$this->pmfLevel->getAttr("spawnY"),(float)$this->pmfLevel->getAttr("spawnZ"));
  }
  public function getGenerator() {
    return "flat";
  }
  public function getGeneratorOptions() {
    return ["preset"=>"2;7,55x1,9x3,2;1;"];
  }

  public static function getFormatName() { return "PMF_1.3"; }
  public static function isValid($path) {
    return (file_exists($path."/level.pmf")
	    && file_exists($path."/entities.yml")
	    && file_exists($path."/tiles.yml")
	    && is_dir($path."/chunks"));
  }
  public function getRegions() { return ["0,0" => [0,0]]; }
  public function getRegion($x, $z) {
    return new RegionLoader($this);
  }
  public function getPMFLevel() { return $this->pmfLevel; }
}
