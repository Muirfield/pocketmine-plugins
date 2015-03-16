<?php
namespace pmimporter;
use pmimporter\LevelFormat;
use pmimporter\ImporterException;

abstract class LevelFormatManager{
  protected static $formats = [];

  /**
   * @param string $class
   */
  public static function addFormat($class){
    if(!is_subclass_of($class, LevelFormat::class))
      throw new ImporterException("Class $class is not a subclass of LevelFormat\n");
    /** @var LevelProvider $class */
    self::$formats[strtolower($class::getFormatName())] = $class;
  }
  public static function getFormatByName($name) {
    $name = trim(strtolower($name));
    return isset(self::$formats[$name]) ? self::$formats[$name] : null;
  }
  /**
   * Returns a LevelFormat class for this path, or null
   *
   * @param string $path
   *
   * @return string
   */
  public static function getFormat($path){
    foreach(self::$formats as $format){
      if($format::isValid($path)){
	return $format;
      }
    }

    return null;
  }
}
