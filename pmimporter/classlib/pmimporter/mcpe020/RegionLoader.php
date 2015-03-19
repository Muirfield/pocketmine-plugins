<?php
namespace pmimporter\mcpe020;
use pmimporter\ImporterException;
use pmimporter\LevelFormat;
use pocketmine\utils\Binary;
use pocketmine_1_3\PocketChunkParser;
use pocketmine\nbt\NBT;


class RegionLoader {
  protected $formatProvider;
  protected $chunks;
  protected $nbt;

  public function getProvider() { return $this->formatProvider; }
  public function getChunks() { return $this->chunks; }

  public function __construct(LevelFormat $fmt) {
    $this->formatProvider = $fmt;
    $this->chunks = new PocketChunkParser();
    $this->chunks->loadFile($fmt->getPath().'chunks.dat');
    $this->chunks->loadMap();
    $this->nbt = [];
    if (file_exists($fmt->getPath().'entities.dat')) {
      $dat = file_get_contents($fmt->getPath().'entities.dat');
      if (substr($dat,0,3) == "ENT" &&  ord(substr($dat,3,1)) == 0 &&
	  Binary::readLInt(substr($dat,4,4)) == 1 &&
	  Binary::readLInt(substr($dat,8,4)) == (strlen($dat) - 12)) {
	$nbt = new NBT(NBT::LITTLE_ENDIAN);
	$nbt->read(substr($dat,12));
	$this->nbt = $nbt->getData();
      }
    }
  }

  public function chunkExists($x,$z) {
    $x -= $this->formatProvider->getSetting("Xoff");
    $z -= $this->formatProvider->getSetting("Zoff");
    return ($x < 16 && $z < 16 && $x >= 0 && $z >= 0);
  }
  public function close(){
    $this->levelProvider = null;
  }
  public function getX(){
    return 0;
  }
  public function getZ(){
    return 0;
  }

  protected function writeChunkData($oX,$oZ,$data) {
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
  public function readChunkData($x,$z) {
    return "$x,$y";
  }
  public function newChunk($cX,$cZ) {
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
  public function writeChunk($oX,$oZ,$chunk) {
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
  public function readChunk($x,$z) {
    $x -= $this->formatProvider->getSetting("Xoff");
    $z -= $this->formatProvider->getSetting("Zoff");
    return new Chunk($this,$x,$z,$this->nbt);
  }
}
