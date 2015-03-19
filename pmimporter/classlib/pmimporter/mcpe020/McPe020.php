<?php
namespace pmimporter\mcpe020;
use pmimporter\LevelFormat;
use pmimporter\ImporterException;
use pocketmine\math\Vector3;
use pocketmine\utils\Binary;
use pocketmine\nbt\NBT;

class McPe020 implements LevelFormat {
  protected $path;
  protected $levelData;

  public function __construct($path,$ro=true,$settings=null) {
    if (!$ro) {
      throw new ImporterException("$path: old skool format only supported read-only\n");
    }
    $path = preg_replace('/\/*$/',"",$path).'/';
    $this->path = $path;

    $nbt = new NBT(NBT::LITTLE_ENDIAN);
    $nbt->read(substr(file_get_contents($this->getPath()."level.dat"),8));
    $this->levelData = $nbt->getData();

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
  private function getAttr($attr,$nbtattr) {
    if (isset($this->settings[$attr])) return $this->settings[$attr];
    return $this->levelData[$nbtattr];
  }
  public function getName() {
    return $this->getAttr("name","LevelName");
  }
  public function getSeed() {
    return $this->getAttr("seed","RandomSeed");
  }
  public function getSpawn() {
    if (isset($this->settings["spawn"])) {
      $spawn = explode(',',$this->settings["spawn"],3);
      if (count($spawn) == 3)
	return new Vector3((float)$spawn[0],(float)$spawn[1],(float)$spawn[2]);
    }
    $x = isset($this->settings["spawnX"]) ? $this->settings["spawnX"]
      : $this->levelData["SpawnX"] + $this->settings["Xoff"] * 16;
    $z = isset($this->settings["spawnZ"]) ? $this->settings["spawnZ"]
      : $this->levelData["SpawnZ"] + $this->settings["Zoff"] * 16;
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

  public static function getFormatName() { return "mcpe0.2.0"; }
  public static function isValid($path) {
    if (file_exists($path."/level.dat") && file_exists($path."/chunks.dat")) {
      $dat = file_get_contents($path.'/level.dat');
      if ((Binary::readLInt(substr($dat,0,4)) == 2
	   || Binary::readLInt(substr($dat,0,4)) == 3)
	  && Binary::readLInt(substr($dat,4,4)) == (strlen($dat) - 8)) 
	return true;
    }
    return false;
  }
  public function getRegions() { return ["0,0" => [0,0]]; }
  public function getRegion($x, $z) {
    return new RegionLoader($this);
  }
}
