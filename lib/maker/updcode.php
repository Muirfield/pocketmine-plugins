<?php
//
// Update a code tree
//
require_once(LIBDIR."maker/fix-common.php");

function update_code($srcdir,$dstdir) {
  $srcdir = preg_replace('/\/*$/',"",$srcdir)."/";
  $dstdir = preg_replace('/\/*$/',"",$dstdir)."/";
  if ($srcdir == "/" || $dstdir == "/") die("Trying to use root dir!\n");

  if (!is_dir($srcdir)) {
    die("Missing directory $srcdir\n");
  }

  //
  // Make an inventory of existing files...
  //
  $dstmanifest = [];
  $dstdirlen = strlen($dstdir);
  $srcdirlen = strlen($srcdir);
  if (is_dir($dstdir)) {
 	  foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dstdir)) as $it){
      if ($it->getFileName() == "..") continue;
      if ($it->getFileName() == ".") {
        $f = substr(dirname($it),$dstdirlen);
        if ($f == "") continue;
      } else {
        $f = substr($it,$dstdirlen);
      }
      $dstmanifest[$f] = $f;
    }
 	}
  //
  // Run through source code....
  //
  foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcdir)) as $it){
    //if ($it->getFileName() == "." || $it->getFileName() == "..") continue;
    if ($it->getFileName() == ".") {
      $f = substr(dirname($it),$srcdirlen);
      if (isset($dstmanifest[$f])) unset($dstmanifest[$f]);
      continue;
    }
    if (!is_file($it)) continue;
    $f = substr($it,$srcdirlen);
    if (isset($dstmanifest[$f])) unset($dstmanifest[$f]);
    if (($ctxt = file_get_contents($srcdir.$f)) === false) continue;
    $otxt = is_file($dstdir.$f) ? file_get_contents($dstdir.$f) : false;
    //echo "$f - ctxt=".strlen($ctxt)." dtxt=".strlen($otxt)."\n";
    if ($ctxt != $otxt) {
      // Text has changed...
      echo "Updating $f\n";
      chkdir($dstdir.$f);
      file_put_contents($dstdir.$f,$ctxt);
    }
  }

 	// Clean-up unused files
  foreach ($dstmanifest as $p) {
    if (is_dir($dstdir.$p)) cotinue;
    echo "Removing $p\n";
    unlink($dstdir.$p);
  }
  foreach ($dstmanifest as $p) {
    if (!is_dir($dstdir.$p)) continue;
    echo "Rmdir $p\n";
    rmdir($dstdir.$p);
  }

}
