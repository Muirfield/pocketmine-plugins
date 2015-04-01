<?php
namespace pmimporter;
use pmimporter\ImporterException;

abstract class Blocks {
  protected static $blockIds = [];
  protected static $blockNames = [];
  protected static $tileIds = [];
  protected static $blockConv = [];

  const INVALID_BLOCK = 248;

  public static function from_camel_case($input) {
    preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
    $ret = $matches[0];
    foreach ($ret as &$match) {
      $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
    }
    return implode('_', $ret);
  }
  public static function __init() {
    if (count(self::$blockIds)) return; // Only read them once...

    // Read block definitions
    if (defined('CLASSLIB_DIR')) {
      $fp = fopen(CLASSLIB_DIR."pmimporter/blocks.txt","r");
    } else {
      $fp = fopen(dirname(realpath(__FILE__))."/blocks.txt","r");
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

	self::$blockNames[$code] = $name;
	self::$blockIds[$name] = $code;

	if ($code >= 0) {
	  $cname = strtoupper(self::from_camel_case($name));
	  define("BL_".$cname,$code);
	} else {
	  self::$blockConv[-$code] = isset($ln[0]) ? $ln[0] : self::INVALID_BLOCK;
	}
      }
      fclose($fp);
    } else {
      throw new ImporterException("Unable to read blocks.txt\n");
    }

    foreach (["Sign","Chest","Furnace"] as $tile) {
      self::$tileIds[$tile] = $tile;
      define("TID_".strtoupper(self::from_camel_case($tile)),$tile);
    }
  }
  public static function getTileId($id) {
    if (isset(self::$tileIds[$id])) return self::$tileIds[$id];
    return null;
  }
  public static function getBlockById($id) {
    if (isset(self::$blockNames[$id])) return self::$blockNames[$id];
    return null;
  }
  public static function getBlockByName($name) {
    if (isset(self::$blockIds[$name])) return self::$blockIds[$name];
    return null;
  }
  public static function addRule($cid,$nid) {
    if ($cid === null || $nid === null) return;
    if ($cid == $nid) return;
    if ($cid < 0) $cid = -$cid;
    if ($nid < 0) return;
    self::$blockConv[$cid] = $nid;
  }

  public static function xlateBlock($id) {
    if (isset(self::$blockConv[$id])) return self::$blockConv[$id];
    if (isset(self::$blockNames[$id])) return $id;
    return self::INVALID_BLOCK;
  }
}
