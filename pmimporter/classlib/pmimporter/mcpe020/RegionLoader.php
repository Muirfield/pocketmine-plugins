<?php
namespace pmimporter\mcpe020;
use pmimporter\ImporterException;
use pmimporter\LevelFormat;
use pocketmine\utils\Binary;
use pocketmine_1_3\PocketChunkParser;

class RegionLoader {
  protected $formatProvider;
  protected $chunks;


  public function __construct(LevelFormat $fmt) {
    $this->formatProvider = $fmt;
    $this->chunks = new PocketChunkParser();
    $this->chunks->loadFile($fmt->getPath().'chunks.dat');
    $this->chunks->loadMap();
  }

  public function chunkExists($x,$z) {
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
    return new Chunk($this->chunks,$x,$z);
  }
}
