<?php
/*
 * Basic hack to import MCPE maps into PMF
 */

/***REM_START***/
require_once(dirname($_SERVER["SCRIPT_FILENAME"])."/src/config.php");

require_once(FILE_PATH."/src/functions.php");
require_once(FILE_PATH."/src/dependencies.php");

foreach ($argv as $map) {
  if (!is_dir($map)) continue;
  if (substr($map,-1,1) != '/') { $map .= '/'; }
  if (!file_exists($map.'level.pmf')) {
    console("[INFO] import $map");
    $level = new LevelImport($map);
    if($level->import() === false){
      console("[INFO] Failed import");
    } else {
      console("[INFO] import done");
    }
  }
}

exit(0);
