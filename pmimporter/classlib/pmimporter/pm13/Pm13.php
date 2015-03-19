<?php
namespace pmimporter\pm13;
use pmimporter\LevelFormat;
use pmimporter\ImporterException;

use pocketmine_1_3\pmf\PMFLevel;

use pocketmine\math\Vector3;

class Pm13 implements LevelFormat {
  protected $path;
  protected $pmfLevel;
  protected $settings;

  public function __construct($path,$ro=true,$settings=null) {
    if (!$ro) {
      throw new ImporterException("$path: old skool format only supported read-only\n");
    }
    $path = preg_replace('/\/*$/',"",$path).'/';
    $this->path = $path;

    $pmfLevel = new PMFLevel($this->getPath()."level.pmf");
    $this->pmfLevel = $pmfLevel;
    if ($settings) {
      $this->settings = $settings;
    } else {
      $this->settings = [];
    }
    if (!isset($this->settings["Xoff"])) $this->settings["Xoff"] = 0;
    if (!isset($this->settings["Zoff"])) $this->settings["Zoff"] = 0;
  }
  public function getPath() {
    return $this->path;
  }

  public static function generate($path, $name, Vector3 $spawn, $seed, $generator, array $options = []) {
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
  public function getSetting($attr) {
    return $this->settings[$attr];
  }
  private function getAttr($attr) {
    if (isset($this->settings[$attr])) return $this->settings[$attr];
    return $this->pmfLevel->getAttr($attr);
  }

  public function getName() {
    return $this->getAttr("name");
  }
  public function getSeed() {
    return $this->getAttr("seed");
  }
  public function getSpawn() {
    if (isset($this->settings["spawn"])) {
      $spawn = explode(',',$this->settings["spawn"],3);
      if (count($spawn) == 3)
	return new Vector3((float)$spawn[0],(float)$spawn[1],(float)$spawn[2]);
    }
    $x = isset($this->settings["spawnX"]) ? $this->settings["spawnX"]
      : $this->pmfLevel->getAttr("spawnX") + $this->settings["Xoff"] * 16;
    $z = isset($this->settings["spawnZ"]) ? $this->settings["spawnZ"]
      : $this->pmfLevel->getAttr("spawnZ") + $this->settings["Zoff"] * 16;
    return new Vector3((float)$x,(float)$this->getAttr("spawnY"),(float)$z);
  }
  public function getGenerator() {
    if (isset($this->settings["generator"]))
      return $this->settings["generator"];
    return "flat";
  }
  public function getGeneratorOptions() {
    if (isset($this->settings["preset"]))
      return ["preset"=>$this->settings["preset"]];
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
