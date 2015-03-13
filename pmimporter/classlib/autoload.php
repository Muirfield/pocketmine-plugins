<?php
if (!defined('CLASSLIB_DIR')) {
  define('CLASSLIB_DIR',dirname(realpath(__FILE__)).'/');
  define('EXTERNAL_OVL',dirname(CLASSLIB_DIR).'/ovl/');
  define('EXTERNAL_PM',dirname(CLASSLIB_DIR).'/pocketmine/');
  // Report all PHP errors
  error_reporting(E_ALL);
}

function __autoload($classname) {
  //echo "autoload $classname\n";
  $file = strtr($classname,"\\","/").".php";
  if (is_readable(CLASSLIB_DIR.$file)) {
    require_once(CLASSLIB_DIR.$file);
    if (method_exists($classname,'__init')) $classname::__init();
    return;
  }
  if (is_readable(EXTERNAL_OVL.$file)) {
    fwrite(STDERR,"local-override: $classname\n");
    require_once(EXTERNAL_OVL.$file);
    return;
  }
  if (is_readable(EXTERNAL_PM.$file)) {
    fwrite(STDERR,"external-pmclass: $classname\n");
    require_once(EXTERNAL_PM.$file);
    return;
  }
  require_once($file);
}

// Some hard coding...
//define("ENDIANNESS", (pack("d", 1) === "\77\360\0\0\0\0\0\0" ? Binary::BIG_ENDIAN : Binary::LITTLE_ENDIAN));
define("ENDIANNESS", (pack("d", 1) === "\77\360\0\0\0\0\0\0" ? 0x00 : 0x01));
define("INT32_MASK", is_int(0xffffffff) ? 0xffffffff : -1);

define("NL","\n");
define("PMIMPORTER_VERSION","1.0");

if(version_compare("5.6.0", PHP_VERSION) > 0)
  die("PHP Version >5.6.0 required - (".PHP_VERSION.")\n");
if(php_sapi_name() !== "cli") die("Must run on CLI API php version\n");

// Other stuff that we want to pre-load...
require_once(CLASSLIB_DIR."pmimporter/Blocks.php");
\pmimporter\Blocks::__init();
require_once(CLASSLIB_DIR."pmimporter/Entities.php");
\pmimporter\Entities::__init();

