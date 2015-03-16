<?php
namespace pmimporter;

interface Chunk {
  /**
   * Gets block and meta
   *
   * @param int $x 0-15
   * @param int $y 0-15
   * @param int $z 0-15
   *
   * @return [ block_id, meta_data ]
   */
  public function getBlock($x, $y, $z);
  /**
   * @param int $x       0-15
   * @param int $y       0-127
   * @param int $z       0-15
   * @param int $blockId , if null, do not change
   * @param int $meta    0-15, if null, do not change
   *
   */
  public function setBlock($x, $y, $z, $blockId = null, $meta = null);
  /**
   * @param int $x 0-15
   * @param int $y 0-127
   * @param int $z 0-15
   *
   * @return int 0-15
   */
  public function getBlockSkyLight($x, $y, $z);

  /**
   * @param int $x     0-15
   * @param int $y     0-127
   * @param int $z     0-15
   * @param int $level 0-15
   */
  public function setBlockSkyLight($x, $y, $z, $level);

  /**
   * @param int $x 0-15
   * @param int $y 0-127
   * @param int $z 0-15
   *
   * @return int 0-15
   */
  public function getBlockLight($x, $y, $z);

  /**
   * @param int $x     0-15
   * @param int $y     0-127
   * @param int $z     0-15
   * @param int $level 0-15
   */
  public function setBlockLight($x, $y, $z, $level);
  /**
   * @param int $x 0-15
   * @param int $z 0-15
   *
   * @return int 0-255
   */
  public function getBiomeId($x, $z);
  /**
   * @param int $x       0-15
   * @param int $z       0-15
   * @param int $biomeId 0-255
   */
  public function setBiomeId($x, $z, $biomeId);
  /**
   * @param int $x 0-15
   * @param int $z 0-15
   *
   * @return int 0-255
   */
  public function getHeightMap($x, $z);
  /**
   * @param int $x 0-15
   * @param int $z 0-15
   * @param $value 0-255
   */
  public function setHeightMap($x, $z, $value);
  /**
   * @return int[]
   */
  public function getHeightMapArray();
  /**
   * @return int[]
   */
  public function getBiomeColorArray();
  /**
   * @param int $x
   * @param int $z
   *
   * @return int[] RGB bytes
   */
  public function getBiomeColor($x, $z);
  /**
   * @param int $x 0-15
   * @param int $z 0-15
   * @param int $R 0-255
   * @param int $G 0-255
   * @param int $B 0-255
   */
  public function setBiomeColor($x, $z, $R, $G, $B);
  public function toBinary();
  /**
   * @param string        $data
   * @return Chunk
   */
  public static function fromBinary($data);

  public function getEntities();
  public function setEntities(array $entities = []);
  public function getTileEntities();
  public function setTileEntities(array $tiles = []);
}
