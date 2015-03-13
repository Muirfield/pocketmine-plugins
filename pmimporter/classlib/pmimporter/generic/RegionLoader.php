<?php
namespace pmimporter\generic;
use pmimporter\ImporterException;
use pmimporter\LevelFormat;
use pocketmine\utils\Binary;


abstract class RegionLoader {
  const VERSION = 1;
  const COMPRESSION_GZIP = 1;
  const COMPRESSION_ZLIB = 2;
  const MAX_SECTOR_LENGTH = 256 << 12; //256 sectors, (1 MB)
  public static $COMPRESSION_LEVEL = 7;

  protected $x;
  protected $z;

  protected $filePath;
  protected $filePointer;
  protected $lastSector;
  protected $formatProvider;
  protected $locationTable = [];
  protected $readOnly;

  protected static function getChunkOffset($x, $z){
    return $x + ($z << 5);
  }

  abstract public function readChunk($x,$z);
  abstract public function newChunk($cX,$cZ);
  abstract public function writeChunk($oX,$oZ,$chunk);

  public function __construct(LevelFormat $fmt,$rX,$rZ,$ext,$ro = false) {
    $this->x = $rX;
    $this->z = $rZ;
    $this->formatProvider = $fmt;
    $this->readOnly = $ro;

    $this->filePath = $this->formatProvider->getPath()."region/r.$rX.$rZ.$ext";
    $exists = file_exists($this->filePath);
    if ($ro) {
      if (!$exists) {
	throw new ImporterException("Region $rX,$rZ does not exist!");
      }
      //echo ("OPENING $this->filePath ReadOnly\n");
      $this->filePointer = fopen($this->filePath,"rb");
    } else {
      //echo ("OPENING $this->filePath RW\n");
      touch($this->filePath);
      $this->filePointer = fopen($this->filePath,"r+b");
      stream_set_write_buffer($this->filePointer,1024*16); // 16KB
    }
    stream_set_read_buffer($this->filePointer,1024*16); // 16KB
    if (!$exists) {
      $this->createBlank();
    } else {
      $this->loadLocationTable();
    }
  }

  public function __destruct() {
    if(is_resource($this->filePointer)) {
      if (!$this->readOnly) $this->writeLocationTable();
      fclose($this->filePointer);
    }
  }

  protected function isChunkPresent($index) {
    return !($this->locationTable[$index][0] === 0 or $this->locationTable[$index][1] === 0);
  }
  public function chunkExists($x,$z) {
    return $this->isChunkPresent(self::getChunkOffset($x,$z));
  }

  public function readChunkData($x,$z) {
    $index = self::getChunkOffset($x, $z);
    if($index < 0 or $index >= 4096) return null;
    if(!$this->isChunkPresent($index)) return null;
    fseek($this->filePointer, $this->locationTable[$index][0] << 12);
    $length = Binary::readInt(fread($this->filePointer, 4));
    $compression = ord(fgetc($this->filePointer));
    if($length <= 0 or $length > self::MAX_SECTOR_LENGTH) return null;
    return fread($this->filePointer,$length-1);
  }

  public function close(){
    if (!$this->readOnly) $this->writeLocationTable();
    fclose($this->filePointer);
    $this->levelProvider = null;
  }

  protected function createBlank(){
    fseek($this->filePointer, 0);
    ftruncate($this->filePointer, 0);
    $this->lastSector = 2;
    $table = "";
    for($i = 0; $i < 1024; ++$i){
      $this->locationTable[$i] = [0, 0];
      $table .= Binary::writeInt(0);
    }

    $time = time();
    for($i = 0; $i < 1024; ++$i){
      $this->locationTable[$i][2] = $time;
      $table .= Binary::writeInt($time);
    }

    fwrite($this->filePointer, $table, 4096 * 2);
  }

  protected function loadLocationTable(){
    fseek($this->filePointer, 0);
    $this->lastSector = 2;

    $table = fread($this->filePointer, 4 * 1024 * 2); //1024 records * 4 bytes * 2 times
    for($i = 0; $i < 1024; ++$i){
      $index = unpack("N", substr($table, $i << 2, 4))[1];
      $this->locationTable[$i] = [$index >> 8, $index & 0xff, unpack("N", substr($table, 4096 + ($i << 2), 4))[1]];
      if(($this->locationTable[$i][0] + $this->locationTable[$i][1]) > $this->lastSector){
	$this->lastSector = $this->locationTable[$i][0] + $this->locationTable[$i][1];
      }
    }
  }
  protected function writeLocationTable(){
    $write = [];

    for($i = 0; $i < 1024; ++$i){
      $write[] = (($this->locationTable[$i][0] << 8) | $this->locationTable[$i][1]);
    }
    for($i = 0; $i < 1024; ++$i){
      $write[] = $this->locationTable[$i][2];
    }
    fseek($this->filePointer, 0);
    fwrite($this->filePointer, pack("N*", ...$write), 4096 * 2);
  }

  public function getX(){
    return $this->x;
  }

  public function getZ(){
    return $this->z;
  }

  protected function writeChunkData($oX,$oZ,$data) {
    if ($this->readOnly) {
      throw new ImporterException("Error trying to write chunk($oX,$oZ) to read-only Level");
    }

    $length = strlen($data) + 1;
    if($length + 4 > self::MAX_SECTOR_LENGTH){
      throw new ImporterException(__CLASS__."::".__METHOD__.": Chunk is too big! ".($length + 4)." > ".self::MAX_SECTOR_LENGTH);
    }
    $sectors = (int) ceil(($length + 4) / 4096);
    $index = self::getChunkOffset($oX, $oZ);

    // We always append data...
    $this->locationTable[$index][0] = $this->lastSector;
    $this->locationTable[$index][1] = $sectors;
    $this->locationTable[$index][2] = time();

    fseek($this->filePointer, $this->locationTable[$index][0] << 12);
    fwrite($this->filePointer, str_pad(Binary::writeInt($length) . chr(self::COMPRESSION_ZLIB) . $data, $sectors << 12, "\x00", STR_PAD_RIGHT));
    // Don't update locationTable until the very end!
  }
}


