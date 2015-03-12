<?php
namespace pmimporter\anvil;
use pocketmine\utils\Binary;
use pmimporter\LevelFormat;
use pmimporter\anvil\Chunk;
use pmimporter\ImporterException;

class RegionLoader extends \pmimporter\mcregion\RegionLoader {
  public function __construct(LevelFormat $fmt,$rX,$rZ) {
    $this->x = $rX;
    $this->z = $rZ;
    $this->formatProvider = $fmt;
    $this->filePath = $this->formatProvider->getPath()."region/r.$rX.$rZ.mca";
    $exists = file_exists($this->filePath);
    touch($this->filePath);
    $this->filePointer = fopen($this->filePath,"r+b");
    stream_set_read_buffer($this->filePointer,1024*16); // 16KB
    stream_set_write_buffer($this->filePointer,1024*16); // 16KB
    if (!$exists) {
      //echo "($rX,$rZ) Create blank location table\n";
      $this->createBlank();
    } else {
      //echo "($rX,$rZ) Read location table\n";
      $this->loadLocationTable();
    }
  }
  public function readChunk($x,$z) {
    $data = $this->readChunkData($x,$z);
    if ($data === null) return null;
    return Chunk::fromBinary($data);
  }

  public function newChunk($oX,$oZ) {
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }
  public function writeChunk($oX,$oZ,$chunk) {
    throw new ImporterException("Unimplemented ".__CLASS__."::".__METHOD__);
  }

}
