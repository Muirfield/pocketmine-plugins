<?php
namespace pmimporter\generic;
use pmimporter\LevelFormat;
use pmimporter\ImporterException;

use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Compound;

abstract class BaseFormat implements LevelFormat {
  protected $path;
  protected $levelData;
  protected $readOnly;

  /**
   * @param string $path
   */
  public function __construct($path,$ro=true) {
    $this->readOnly = $ro;
    if (!is_dir($path))
      throw new ImporterException("$path: path does not exist\n");
    $path = preg_replace('/\/*$/',"",$path).'/';
    $this->path = $path;

    $nbt = new NBT(NBT::BIG_ENDIAN);
    $nbt->readCompressed(file_get_contents($this->getPath()."level.dat"));
    $levelData = $nbt->getData();
    if ($levelData->Data instanceof Compound){
      $this->levelData = $levelData->Data;
    } else {
      throw new ImporterException("Invalid level.dat\n");
    }
  }
  public function getPath() {
    return $this->path;
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
    return $this->levelData["generatorName"];
  }
  public function getGeneratorOptions() {
    return ["preset"=>$this->levelData["generatorOptions"]];
  }
  /**
   * @return Compound
   */
  public function getLevelData(){
    return $this->levelData;
  }
  public function saveLevelData(){
    $nbt = new NBT(NBT::BIG_ENDIAN);
    $nbt->setData(new Compound(null, [
				      "Data" => $this->levelData
				      ]));
    $buffer = $nbt->writeCompressed();
    file_put_contents($this->getPath() . "level.dat", $buffer);
  }
}
