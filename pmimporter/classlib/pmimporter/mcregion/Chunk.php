<?php
namespace pmimporter\mcregion;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\ByteArray;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\IntArray;
use pocketmine\utils\Binary;

class Chunk extends \pmimporter\generic\Chunk {
  protected $blocks;
  protected $data;
  protected $skyLight;
  protected $blockLight;

  public function __construct(Compound $nbt){
    parent::__construct($nbt);

    if(!isset($this->nbt->Blocks)){
      $this->nbt->Blocks = new ByteArray("Blocks", str_repeat("\x00", 32768));
    }
    if(!isset($this->nbt->Data)){
      $this->nbt->Data = new ByteArray("Data", $half = str_repeat("\x00", 16384));
      $this->nbt->SkyLight = new ByteArray("SkyLight", $half);
      $this->nbt->BlockLight = new ByteArray("BlockLight", $half);
    }
    $this->blocks = $this->nbt->Blocks->getValue();
    $this->data = $this->nbt->Data->getValue();
    $this->skyLight = $this->nbt->SkyLight->getValue();
    $this->blockLight = $this->nbt->BlockLight->getValue();

    unset($this->nbt->Blocks);
    unset($this->nbt->Data);
    unset($this->nbt->SkyLight);
    unset($this->nbt->BlockLight);
  }

  public function getBlock($x, $y, $z) {
    $i = ($x << 11) | ($z << 7) | $y;
    $blockId = ord($this->blocks{$i});
    if (($y & 1) == 0) {
      $meta = ord($this->data{$i >> 1}) & 0x0F;
    } else {
      $meta = ord($this->data{$i >> 1}) >> 4;
    }
    return [ $blockId,$meta ];
  }

  public function setBlock($x, $y, $z, $blockId = null, $meta = null){
    $i = ($x << 11) | ($z << 7) | $y;

    if($blockId !== null){
      $blockId = chr($blockId);
      if($this->blocks{$i} !== $blockId){
	$this->blocks{$i} = $blockId;
      }
    }

    if($meta !== null){
      $i >>= 1;
      $old_m = ord($this->data{$i});
      if(($y & 1) === 0){
	$this->data{$i} = chr(($old_m & 0xf0) | ($meta & 0x0f));
      }else{
	$this->data{$i} = chr((($meta & 0x0f) << 4) | ($old_m & 0x0f));
      }
    }
  }
  public function getBlockSkyLight($x, $y, $z) {
    $sl = ord($this->skyLight{($x << 10) | ($z << 6) | ($y >> 1)});
    if(($y & 1) === 0){
      return $sl & 0x0F;
    }else{
      return $sl >> 4;
    }
  }
  public function setBlockSkyLight($x, $y, $z, $level) {
    $i = ($x << 10) | ($z << 6) | ($y >> 1);
    $old_sl = ord($this->skyLight{$i});
    if(($y & 1) === 0){
      $this->skyLight{$i} = chr(($old_sl & 0xf0) | ($level & 0x0f));
    }else{
      $this->skyLight{$i} = chr((($level & 0x0f) << 4) | ($old_sl & 0x0f));
    }
  }
  public function getBlockLight($x, $y, $z) {
    $l = ord($this->blockLight{($x << 10) | ($z << 6) | ($y >> 1)});
    if(($y & 1) === 0){
      return $l & 0x0F;
    }else{
      return $l >> 4;
    }
  }
  public function setBlockLight($x, $y, $z, $level) {
    $i = ($x << 10) | ($z << 6) | ($y >> 1);
    $old_l = ord($this->blockLight{$i});
    if(($y & 1) === 0){
      $this->blockLight{$i} = chr(($old_l & 0xf0) | ($level & 0x0f));
    }else{
      $this->blockLight{$i} = chr((($level & 0x0f) << 4) | ($old_l & 0x0f));
    }
    $this->hasChanged = true;
  }

  /**
   * @param string        $data
   * @param LevelProvider $provider
   *
   * @return Chunk
   */
  public static function fromBinary($data) {
    $nbt = new NBT(NBT::BIG_ENDIAN);
    $nbt->readCompressed($data, ZLIB_ENCODING_DEFLATE);
    $chunk = $nbt->getData();

    if(!isset($chunk->Level) or !($chunk->Level instanceof Compound)){
      return null;
    }
    $x = $chunk->Level->Blocks->getValue();
    return new Chunk($chunk->Level);
  }

  public function toBinary(){
    $nbt = clone $this->getNBT();

    $nbt->xPos = new Int("xPos", $this->x);
    $nbt->zPos = new Int("zPos", $this->z);

    if($this->isGenerated()){
      $nbt->Blocks = new ByteArray("Blocks", $this->blocks);
      $nbt->Data = new ByteArray("Data", $this->data);
      $nbt->SkyLight = new ByteArray("SkyLight", $this->skyLight);
      $nbt->BlockLight = new ByteArray("BlockLight", $this->blockLight);

      $nbt->Biomes = new ByteArray("Biomes", $this->getBiomeIdArray());
      $nbt->BiomeColors = new IntArray("BiomeColors", $this->getBiomeColorArray());

      $nbt->HeightMap = new IntArray("HeightMap", $this->getHeightMapArray());
    }

    /*
    $entities = [];
    $nbt->Entities = new Enum("Entitie


    foreach($this->getEntities() as $entity){
      if(!($entity instanceof Player) and !$entity->closed){
	$entity->saveNBT();
	$entities[] = $entity->namedtag;
      }
    }

    $nbt->Entities = new Enum("Entities", $entities);
    $nbt->Entities->setTagType(NBT::TAG_Compound);
    */
    $entities = [];
    foreach($this->getEntities() as $entity) {
      $entities[] = clone $entity;
    }
    $nbt->Entities = new Enum("Entities",$entities);
    $nbt->Entities->setTagType(NBT::TAG_Compound);

    $tiles = [];
    foreach($this->getTileEntities() as $tile) {
      $tiles[] = clone $tile;
    }
    $nbt->TileEntities = new Enum("TileEntities",$tiles);
    $nbt->TileEntities->setTagType(NBT::TAG_Compound);

    $writer = new NBT(NBT::BIG_ENDIAN);
    $nbt->setName("Level");
    $writer->setData(new Compound("", ["Level" => $nbt]));
    return $writer->writeCompressed(ZLIB_ENCODING_DEFLATE, RegionLoader::$COMPRESSION_LEVEL);
  }

}