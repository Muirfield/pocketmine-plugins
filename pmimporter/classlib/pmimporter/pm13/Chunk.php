<?php
namespace pmimporter\pm13;
use pocketmine_1_3\pmf\PMFLevel;

use pocketmine\utils\Binary;
use pmimporter\ImporterException;

class Chunk implements \pmimporter\Chunk {
  protected $x;
  protected $z;
  protected $chunks;

  protected function getXPos($x) {
    return ($this->x << 4)+$x;
  }
  protected function getZPos($z) {
    return ($this->z << 4)+$z;
  }

  public function __construct(PMFLevel $chunks,$x,$z) {
    $this->chunks = $chunks;
    $this->x = $x;
    $this->z = $z;
  }

  public function getBiomeId($x, $z) { return 1; }
  public function setBiomeId($x, $z, $biomeId) {
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
  public function getBiomeIdArray(){ return str_repeat("\x01", 256); }
  public function getBiomeColorArray() {
    return array_fill(0, 256, Binary::readInt("\x00\x85\xb2\x4a"));
  }
  public function getBiomeColor($x, $z) {
    $color = Binary::readInt("\x00\x85\xb2\x4a") & 0xFFFFFF;
    return [$color >> 16, ($color >> 8) & 0xFF, $color & 0xFF];
  }
  public function setBiomeColor($x, $z, $R, $G, $B) {
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
  protected function getFloor($x,$z) {
    for ($y=127;$y<=0;--$y) {
      if ($this->chuncks->getBlockID($x,$y,$z)) break;
    }
    return $y;
  }
  public function getHeightMap($x, $z) {
    $this->getFloor($this->getXPos($x),$this->getZPos($z));
  }
  public function setHeightMap($x, $z, $value) {
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
  public function getHeightMapArray() {
    $map = [];
    for ($z=0;$z < 16; $z++) {
      for ($x=0;$x<16;$x++) {
	$map[($z << 4) + $x] =
	  $this->getFloor($this->getXPos($x),$this->getZPos($z));
      }
    }
    return $map;
  }
  public function getEntities() { return []; }
  public function setEntities(array $entities = []) {
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
  public function getTileEntities() { return []; }
  public function setTileEntities(array $tiles = []) {
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
  public function isPopulated(){ return 1;}
  public function setPopulated($value = 1){
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
  public function isGenerated(){ return 1; }
  public function setGenerated($value = 1){
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
  public function getBlock($x, $y, $z) {
    return $this->chunks->getBlock($this->getXPos($x),$y,$this->getZPos($z));
  }
  public function setBlock($x, $y, $z, $blockId = null, $meta = null){
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
  public function getBlockSkyLight($x, $y, $z) { return 0x0e; }
  public function setBlockSkyLight($x, $y, $z, $level) {
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
  public function getBlockLight($x, $y, $z) { return 0x0e; }
  public function setBlockLight($x, $y, $z, $level) {
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }

  /**
   * @param string        $data
   * @param LevelProvider $provider
   *
   * @return Chunk
   */
  public static function fromBinary($data) {
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
  public function toBinary(){
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
}
