<?php
namespace pmimporter\mcpe020;
use pocketmine_1_3\PocketChunkParser;
use pocketmine\utils\Binary;
use pmimporter\ImporterException;
use pmimporter\Entities;
use pocketmine\nbt\tag\String;

class Chunk implements \pmimporter\Chunk {
  protected $x;
  protected $z;
  protected $chunks;
  protected $tiles;
  protected $entities;

  protected function getXPos($x) {
    return ($this->x << 4)+$x;
  }
  protected function getZPos($z) {
    return ($this->z << 4)+$z;
  }

  public function __construct(PocketChunkParser $chunks,$x,$z,$nbt) {
    $this->chunks = $chunks;
    $this->x = $x;
    $this->z = $z;
    if (isset($nbt["TileEntities"])) {
      $this->tiles = $nbt["TileEntities"]->getValue();
    }
    if (isset($nbt["Entities"])) {
      $this->entities = $nbt["Entities"]->getValue();
    }
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
  public function getHeightMap($x, $z) {
    return $this->chunks->getFloor($this->getXPos($x),$this->getZPos($z));
  }
  public function setHeightMap($x, $z, $value) {
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
  public function getHeightMapArray() {
    $map = [];
    for ($z=0;$z < 16; $z++) {
      for ($x=0;$x<16;$x++) {
	$map[($z << 4) + $x] =
	  $this->chunks->getFloor($this->getXPos($x),$this->getZPos($z));
      }
    }
    return $map;
  }
  public function getEntities() {
    $entities = [];

    $min_x = $this->getXPos(0); $min_z = $this->getZPos(0);
    $max_x = $this->getXPos(15); $max_z = $this->getZPos(15);

    foreach ($this->entities as $ent) {
      if (!isset($ent->Pos) || !isset($ent->id)) continue;
      $id = Entities::getEntityById($ent->id->getValue());
      if ($id == null) continue;
      if (count($ent->Pos) != 3) continue;
      $x = (int)$ent->Pos[0];
      $y = (int)$ent->Pos[1];
      $z = (int)$ent->Pos[2];
      if ($x < $min_x || $x > $max_x || $z < $min_z || $z > $max_z) continue;
      // Conversion
      $cc = clone $ent;
      $cc->id = new String("id",$id);
      $entities[] = $cc;
    }
    return $entities;
  }
  public function setEntities(array $entities = []) {
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
  public function getTileEntities() {
    $tiles = [];

    $min_x = $this->getXPos(0); $min_z = $this->getZPos(0);
    $max_x = $this->getXPos(15); $max_z = $this->getZPos(15);

    foreach ($this->tiles as $tile) {
      if (isset($tile->x) && isset($tile->y) && isset($tile->z)) {
	if ($tile->x->getValue() < $min_x || $tile->x->getValue() > $max_x ||
	    $tile->z->getValue() < $min_z || $tile->z->getValue() > $max_z) 
	  continue;
	// Straight copy.
	$tiles[] = clone $tile;
      }
    }
    return $tiles;
  }
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
  public function getBlockSkyLight($x, $y, $z) {
    return $this->chunks->getBlockSkyLight($this->getXPos($x),$y,$this->getZPos($z));
  }
  public function setBlockSkyLight($x, $y, $z, $level) {
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
  public function getBlockLight($x, $y, $z) {
    return $this->chunks->getBlockLight($this->getXPos($x),$y,$this->getZPos($z));
  }
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
