<?php
namespace pmimporter\pm13;
use pocketmine_1_3\pmf\PMFLevel;
use pmimporter\pm13\Pm13;

use pocketmine\utils\Binary;
use pmimporter\ImporterException;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\String;
use pocketmine\nbt\tag\Short;

class Chunk implements \pmimporter\Chunk {
  protected $x;
  protected $z;
  protected $chunks;
  protected $adjX;
  protected $adjZ;

  protected function getXPos($x) {
    return ($this->x << 4)+$x;
  }
  protected function getZPos($z) {
    return ($this->z << 4)+$z;
  }

  public function __construct(Pm13 $fmt,$x,$z) {
    $this->chunks = $fmt->getPMFLevel();
    $this->x = $x;
    $this->z = $z;
    $this->adjX = $fmt->getSetting("Xoff") << 4;
    $this->adjZ = $fmt->getSetting("Zoff") << 4;

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
    for ($y=127;$y>0;--$y) {
      if ($this->chunks->getBlockID($x,$y,$z)) break;
    }
    return $y;
  }
  public function getHeightMap($x, $z) {
    return $this->getFloor($this->getXPos($x),$this->getZPos($z));
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
  protected static function convertInventory($name,$src) {
    $items = [];
    if (isset($src)) {
      foreach ($src as $sl) {
	$items[] = new Compound(false,[new Byte("Count",$sl["Count"]),
				       new Byte("Slot",$sl["Slot"]),
				       new Short("id",$sl["id"]),
				       new Short("Damage",$sl["Damage"])]);
      }
    }
    $nbt = new Enum($name,$items);
    $nbt->setTagType(NBT::TAG_Compound);
    return $nbt;
  }
  public function getTileEntities() {
    $tiles = [];
    $yml = dirname($this->chunks->getFile())."/tiles.yml";
    if (!file_exists($yml)) return $tiles;
    $yml = yaml_parse_file($yml);
    $min_x = $this->getXPos(0); $min_z = $this->getZPos(0);
    $max_x = $this->getXPos(15); $max_z = $this->getZPos(15);
    foreach ($yml as $tile) {
      if (!isset($tile["id"])) continue;
      if (isset($tile["x"]) && isset($tile["y"]) && isset($tile["z"])) {
	if ($tile["x"] < $min_x || $tile["x"] > $max_x ||
	    $tile["z"] < $min_z || $tile["z"] > $max_z) continue;
      } else {
	continue;
      }
      switch ($tile["id"]) {
      case TID_SIGN:
	$tiles[] =  new Compound("",[new String("id",TID_SIGN),
				     new String("Text1",$tile["Text1"]),
				     new String("Text2",$tile["Text2"]),
				     new String("Text3",$tile["Text3"]),
				     new String("Text4",$tile["Text4"]),
				     new Int("x",$tile["x"]+$this->adjX),
				     new Int("y",$tile["y"]),
				     new Int("z",$tile["z"]+$this->adjZ)]);
	break;
      case TID_FURNACE:
	$tiles[] = new Compound("",[new String("id",TID_FURNACE),
				    new Short("BurnTime",$tile["BurnTime"]),
				    new Short("BurnTicks",$tile["BurnTicks"]),
				    new Short("CookTime",$tile["CookTime"]),
				    new Short("CookTimeTotal",$tile["MaxTime"]),
				    self::convertInventory("Items",$tile["Items"]),
				    new Int("x",$tile["x"]+$this->adjX),
				    new Int("y",$tile["y"]),
				    new Int("z",$tile["z"]+$this->adjZ)]);
	break;
      case TID_CHEST:
	$chest = [new String("id",TID_CHEST),
		  self::convertInventory("Items",$tile["Items"]),
		  new Int("x",$tile["x"]+$this->adjX),
		  new Int("y",$tile["y"]),
		  new Int("z",$tile["z"]+$this->adjZ)];
	if (isset($tile["pairx"]))
	  $chest[] = new Int("pairx",$tile["pairx"]+$this->adjX);
	if (isset($tile["pairz"]))
	  $chest[] = new Int("pairz",$tile["pairz"]+$this->adjZ);
	$tiles[] = new Compound("",$chest);
	break;
      default:
	// Not supported tile Id
	continue;
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
