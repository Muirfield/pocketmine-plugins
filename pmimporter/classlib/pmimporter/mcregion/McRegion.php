<?php
namespace pmimporter\mcregion;
use pmimporter\generic\BaseFormat;
use pmimporter\mcregion\RegionLoader;

use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\Long;
use pocketmine\nbt\tag\String;
use pocketmine\math\Vector3;

class McRegion extends BaseFormat {
  protected $regions = null;

  public static function getFormatName() {
    return "mcregion";
  }
  public static function isValid($path) {
    $isValid = (file_exists($path."/level.dat") && is_dir($path."/region/"));
    if ($isValid) {
      $files = glob($path."/region/*.mc*");
      foreach($files as $f) {
	if (substr($f,-4) != ".mcr") {// not McRegion...
	  $isValid = false;
	  break;
	}
      }
    }
    return $isValid;
  }
  public static function generate($path,$name,Vector3 $spawn,$seed,$generator,array $options = []) {
    if (!file_exists($path)) mkdir($path,0777,true);
    if (!file_exists($path."/region")) mkdir($path."/region",0777);
    $levelData = new Compound("Data",
			      [
			       "hardcore"=>new Byte("hardcore",0),
			       "initialized"=>new Byte("initialized",1),
			       "GameType"=>new Int("GameType",0),
			       "generatorVersion"=>new Int("generatorVersion",1), // 2 in MCPE
			       "SpawnX" => new Int("SpawnX",$spawn->x),
			       "SpawnY" => new Int("SpawnY",$spawn->y),
			       "SpawnZ" => new Int("SpawnZ",$spawn->z),
			       "version" => new Int("version",19133),
			       "DayTime" => new Int("DayTime",0),
			       "LastPlayed" => new Long("LastPlayed",microtime(true)*1000),
			       "RandomSeed" => new Long("RandomSeed",$seed),
			       "SizeOnDisk" => new Long("SizeOnDisk",0),
			       "Time" => new Long("Time",0),
			       "generatorName" => new String("generatorName",$generator),
			       "generatorOptions" => new String("generatorOptions",isset($options["preset"]) ?  $options["preset"] : ""),
			       "LevelName" => new String("LevelName",$name),
			       "GameRules" => new Compound("GameRules",[])
			       ]);
    $nbt = new NBT(NBT::BIG_ENDIAN);
    $nbt->setData(new Compound(null,["Data"=>$levelData]));
    $buffer = $nbt->writeCompressed();
    file_put_contents($path."/level.dat",$buffer);
  }
  public function getRegions() {
    if ($this->regions === null) {
      $this->regions = [];
      $files = glob($this->getPath()."region/r.*.mcr");
      foreach ($files as $f) {
	$pp = [];
	if (preg_match('/r\.(-?\d+)\.(-?\d+)\.mcr$/',$f,$pp)) {
	  array_shift($pp);
	  $this->regions[$pp[0].",".$pp[1]] = $pp;
	}
      }
    }
    return $this->regions;
  }

  public function getRegion($x,$z) {
    return new RegionLoader($this,$x,$z,"mcr",$this->readOnly);
  }
}
