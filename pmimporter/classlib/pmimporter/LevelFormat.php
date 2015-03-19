<?php
namespace pmimporter;
use pocketmine\math\Vector3;

interface LevelFormat {
  const ORDER_YZX = 0;
  const ORDER_ZXY = 1;

  /**
   * @param string $path
   * @param bool $ro
   * @param mixed $settings
   */
  public function __construct($path,$ro=true,$settings=null);
  /**
   * Returns the full provider name, like "anvil" or "mcregion", will be used
   * to find the correct format.
   *
   * @return string
   */
  public static function getFormatName();
  /**
   * Tells if the path is a valid level.
   * This must tell if the current format supports opening the files in the
   * directory
   *
   * @param string $path
   *
   * @return true
   */
  public static function isValid($path);
  /**
   * Generate the needed files in the path given
   *
   * @param string  $path
   * @param string  $name
   * @param Vector3 $spawn
   * @param int     $seed
   * @param string  $generator
   * @param array[] $options
   */
  public static function generate($path, $name, Vector3 $spawn, $seed, $generator, array $options = []);
  /** @return string */
  public function getPath();
  /**
   * Returns the generator name
   *
   * @return string
   */
  public function getGenerator();

  /**
   * @return array
   */
  public function getGeneratorOptions();
  /**
   * @return string
   */
  public function getName();
  /**
   * @return int
   */
  public function getSeed();

  /**
   * @return Vector3
   */
  public function getSpawn();

  /**
   * @return [] region list
   */
  public function getRegions();

  /**
   * @param $x
   * @param $z
   *
   * @return RegionLoader
   */
  public function getRegion($x, $z);
}
