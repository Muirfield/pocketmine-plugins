<?php
namespace pmimporter\generic;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\ByteArray;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\IntArray;
use pocketmine\utils\Binary;

abstract class Chunk implements \pmimporter\Chunk {
  /** @var Compound */
  protected $nbt;

  /** @var string */
  protected $biomeIds;
  /** @var int[256] */
  protected $biomeColors;
  protected $heightMap;

  protected $NBTtiles;
  protected $NBTentities;

  protected $x;
  protected $z;

  protected function __construct(Compound $nbt) {
    $this->nbt = $nbt;

    $this->x = $nbt->xPos->getValue();
    $this->z = $nbt->zPos->getValue();

    if(!isset($this->nbt->Biomes) or !($this->nbt->Biomes instanceof ByteArray)){
      $this->nbt->Biomes = new ByteArray("Biomes", str_repeat("\x01", 256));
    }
    $biomeIds = $this->nbt->Biomes->getValue();
    if(strlen($biomeIds) === 256){
      $this->biomeIds = $biomeIds;
    }else{
      $this->biomeIds = str_repeat("\x01", 256);
    }
    unset($this->nbt->Biomes);

    if(!isset($this->nbt->BiomeColors) or !($this->nbt->BiomeColors instanceof IntArray)){
      $this->nbt->BiomeColors = new IntArray("BiomeColors", array_fill(0, 256, Binary::readInt("\x00\x85\xb2\x4a")));
    }
    $biomeColors = $this->nbt->BiomeColors->getValue();
    if(count($biomeColors) === 256){
      $this->biomeColors = $biomeColors;
    }else{
      $this->biomeColors = array_fill(0, 256, Binary::readInt("\x00\x85\xb2\x4a"));
    }
    unset($this->nbt->BiomeColors);

    if(!isset($this->nbt->HeightMap) or !($this->nbt->HeightMap instanceof IntArray)){
      $this->nbt->HeightMap = new IntArray("HeightMap", array_fill(0, 256, 127));
    }
    $heightMap = $this->nbt->HeightMap->getValue();
    if(count($heightMap) === 256){
      $this->heightMap = $heightMap;
    }else{
      $this->heightMap = array_fill(0, 256, 127);
    }
    unset($this->nbt->HeightMap);


    if(isset($this->nbt->Entities) and $this->nbt->Entities instanceof Enum){
      $this->nbt->Entities->setTagType(NBT::TAG_Compound);
    }else{
      $this->nbt->Entities = new Enum("Entities", []);
      $this->nbt->Entities->setTagType(NBT::TAG_Compound);
    }
    $this->NBTentities = $this->nbt->Entities->getValue();
    unset($this->nbt->Entities);

    if(isset($this->nbt->TileEntities) and $this->nbt->TileEntities instanceof Enum){
      $this->nbt->TileEntities->setTagType(NBT::TAG_Compound);
    }else{
      $this->nbt->TileEntities = new Enum("TileEntities", []);
      $this->nbt->TileEntities->setTagType(NBT::TAG_Compound);
    }
    $this->NBTtiles = $this->nbt->TileEntities->getValue();
    unset($this->nbt->TileEntities);
  }

  public function getBiomeId($x, $z) {
    return ord($this->biomeIds{($z << 4) + $x});
  }
  public function setBiomeId($x, $z, $biomeId) {
    $this->biomeIds{($z << 4) + $x} = chr($biomeId);
  }
  public function getBiomeIdArray(){
    return $this->biomeIds;
  }

  public function getHeightMap($x, $z) {
    return $this->heightMap[($z << 4) + $x];
  }
  public function setHeightMap($x, $z, $value) {
    $this->heightMap[($z << 4) + $x] = $value;
  }
  public function getHeightMapArray() {
    return $this->heightMap;
  }
  public function getBiomeColorArray() {
    return $this->biomeColors;
  }
  public function getBiomeColor($x, $z) {
    $color = $this->biomeColors[($z << 4) + $x] & 0xFFFFFF;
    return [$color >> 16, ($color >> 8) & 0xFF, $color & 0xFF];
  }
  public function setBiomeColor($x, $z, $R, $G, $B) {
    $this->biomeColors[($z << 4) + $x] = 0 | (($R & 0xFF) << 16) | (($G & 0xFF) << 8) | ($B & 0xFF);

  }
  public function getNBT() {
    return $this->nbt;
  }
  public function getEntities() {
    return $this->NBTentities;
  }
  public function setEntities(array $entities = []) {
    $this->NBTentities = $entities;
  }
  public function getTileEntities() {
    return $this->NBTtiles;
  }
  public function setTileEntities(array $tiles = []) {
    $this->NBTtiles = $tiles;
  }

  /**
   * @return bool
   */
  public function isPopulated(){
    return $this->nbt["TerrainPopulated"] > 0;
  }
  /**
   * @param int $value
   */
  public function setPopulated($value = 1){
    $this->nbt->TerrainPopulated = new Byte("TerrainPopulated", $value);
  }
  /**
   * @return bool
   */
  public function isGenerated(){
    return $this->nbt["TerrainPopulated"] > 0 or (isset($this->nbt->TerrainGenerated) and $this->nbt["TerrainGenerated"] > 0);
  }

  /**
   * @param int $value
   */
  public function setGenerated($value = 1){
    $this->nbt->TerrainGenerated = new Byte("TerrainGenerated", $value);
  }


}