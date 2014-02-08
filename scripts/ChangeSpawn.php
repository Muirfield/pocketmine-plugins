<?php
/* Change the spanw location of a map */
require_once(dirname(__FILE__)."/src/config.php");

require_once(FILE_PATH."/src/functions.php");
require_once(FILE_PATH."/src/dependencies.php");


array_shift($argv);// Get rid of command name...

$map = array_shift($argv);
if (is_dir($map)) {
  if (substr($map,-1,1) != '/') { $map .= '/'; }
  if (file_exists($map.'level.pmf')) {

    $level = new PMFLevel($map.'level.pmf');
    console("[DEBUG] Current Spawn:");
    console("[DEBUG]   spawnX = ".$level->getData('spawnX'));
    console("[DEBUG]   spawnY = ".$level->getData('spawnY'));
    console("[DEBUG]   spawnZ = ".$level->getData('spawnZ'));

    if (count($argv) == 3) {
      list($x,$y,$z) = $argv;
      console("[DEBUG] SPAWN: $x $y $z");

      $level->setData('spawnX',$x);
      $level->setData('spawnY',$y);
      $level->setData('spawnZ',$z);

      $level->saveData();
      exit(0);
    } else {
      console("[ERROR] No coordinates specified");
      exit(1);
    }
  }
}

console("[ERROR] $map not found");
exit(1);
?>

    console("[INFO] import $map");
    $level = new LevelImport($map);
    if($level->import() === false){
      console("[INFO] Failed import");
    } else {
      console("[INFO] import done");
    }
  }
}

console('[INFO] DATA_PATH='.DATA_PATH);
$api->loadLevel('The Pyramid');

