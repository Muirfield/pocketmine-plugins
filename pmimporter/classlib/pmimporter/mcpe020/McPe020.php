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

  public function __construct($path,$ro=true) {
    if (!$ro) {
      throw new ImporterException("$path: old skool format only supported read-only\n");
    }
    $path = preg_replace('/\/*$/',"",$path).'/';
    $this->path = $path;

    $nbt = new NBT(NBT::LITTLE_ENDIAN);
    $nbt->read(substr(file_get_contents($this->getPath()."level.dat"),8));
    $this->levelData = $nbt->getData();
  }
  public function getPath() {
    return $this->path;
  }

  public static function generate($path, $name, Vector3 $spawn, $seed, $generator, array $options = []) {
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
  public function getName() {
    return $this->levelData["LevelName"];
  }
  public function getSeed() {
    return $this->levelData["RandomSeed"];
  }
  public function getSpawn() {
    return new Vector3((float)$this->levelData["SpawnX"],(float)$this->levelData["SpawnY"],(float)$this->levelData["SpawnZ"]);
  }
  public function getGenerator() {
    return "flat";
  }
  public function getGeneratorOptions() {
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
