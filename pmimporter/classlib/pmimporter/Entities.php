<?php
namespace pmimporter;
use pmimporter\Blocks;


abstract class Entities {
  protected static $entityIds = [];
  protected static $entityNames = [];

  public static function __init() {
    if (count(self::$entityIds)) return; // Only read them once...
    if (defined('CLASSLIB_DIR')) {
      $fp = fopen(CLASSLIB_DIR."pmimporter/entities.txt","r");
    } else {
      $fp = fopen(dirname(realpath(__FILE__))."/entities.txt","r");
    }
    if ($fp) {
      while (($ln = fgets($fp)) !== false) {
	if (preg_match('/^\s*[#;]/',$ln)) continue; // Skip comments
	$ln = preg_replace('/^\s+/','',$ln);
	$ln = preg_replace('/\s+$/','',$ln);
	if ($ln == '') continue;	// Skip empty lines
	$ln = preg_split('/\s+/',$ln);
	if ($ln < 2) continue;

	$code = array_shift($ln);
	$name = array_shift($ln);

	self::$entityIds[$name] = $code;
	if ($code) {
	  self::$entityNames[$code] = $name;
	  define("EID_".strtoupper(Blocks::from_camel_case($name)),$code);
	}
      }
      fclose($fp);
    }
  }
  public static function getId($id) {
    if (isset(self::$entityIds[$id]) && self::$entityIds[$id] > 0) return $id;
    return null;
  }
  public static function getEntityId($name) {
    if (isset(self::$entityIds[$name]) && self::$entityIds[$name] > 0)
      return self::$entityIds[$name];
    return null;
  }
  public static function getEntityById($id) {
    if (isset(self::$entityNames[$id])) return self::$entityNames[$id];
    return null;
  }
}
