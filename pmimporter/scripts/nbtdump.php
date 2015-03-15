<?php
if (!defined('CLASSLIB_DIR'))
  require_once(dirname(realpath(__FILE__)).'/../classlib/autoload.php');

use pocketmine\nbt\NBT;
use pocketmine\utils\Binary;

define('CMD',array_shift($argv));
$file=array_shift($argv);
if (!isset($file)) die("No file specified\n");
if (!is_file($file)) die("$file: does not exist\n");

/* Add support for oldstyle level.dat files... */
$dat = file_get_contents($file);

if ((Binary::readLInt(substr($dat,0,4)) == 2
     || Binary::readLInt(substr($dat,0,4)) == 3)
    && Binary::readLInt(substr($dat,4,4)) == (strlen($dat) - 8)) {
  $nbt = new NBT(NBT::LITTLE_ENDIAN);
  $nbt->read(substr($dat,8));
} else {
  $nbt = new NBT(NBT::BIG_ENDIAN);
  $nbt->readCompressed($dat);
}
$levelData = $nbt->getData();
print_r($levelData);
