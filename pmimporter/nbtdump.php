<?php
require_once(dirname(realpath(__FILE__)).'/classlib/autoload.php');

use pocketmine\nbt\NBT;

define('CMD',array_shift($argv));
$file=array_shift($argv);
if (!isset($file)) die("No file specified\n");
if (!is_file($file)) die("$file: does not exist\n");

$nbt = new NBT(NBT::BIG_ENDIAN);
$nbt->readCompressed(file_get_contents($file));
$levelData = $nbt->getData();
print_r($levelData);
