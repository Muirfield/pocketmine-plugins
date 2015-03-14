<?php
/*
 * Build script
 */
error_reporting(E_ALL);

$p = new Phar('pmimporter.phar',
	      FilesystemIterator::CURRENT_AS_FILEINFO
	      | FilesystemIterator::KEY_AS_FILENAME,
	      'pmimporter.phar');
// issue the Phar::startBuffering() method call to buffer changes made to the
// archive until you issue the Phar::stopBuffering() command
$p->startBuffering();

// set the Phar file stub
// the file stub is merely a small segment of code that gets run initially 
// when the Phar file is loaded, and it always ends with a __HALT_COMPILER()

$p->setStub('<?php Phar::mapPhar(); include "phar://pmimporter.phar/main.php"; __HALT_COMPILER(); ?>');

foreach (['main.php'] as $f) {
  echo ("- $f\n");
  $p[$f] = file_get_contents($f);
}

$help = "Available sub-commands:\n";
foreach (glob('scripts/*.php') as $f) {
  $f = preg_replace('/^scripts\//','',$f);
  $f = preg_replace('/\.php$/','',$f);
  $help .= "\t$f\n";
}
$help .= "\tversion\n";
$help .= "\treadme\n";
$p['scripts/help.php'] = $help;
$p['scripts/version.php'] = "<?php echo PMIMPORTER_VERSION.NL;";
$p['scripts/readme.php'] = file_get_contents('README.md');

$dirs=['classlib','scripts'];
while(count($dirs)) {
  $d = array_shift($dirs);
  $dh = opendir($d) or die("$d: unable to open directory\n");
  while (false !== ($f = readdir($dh))) {
    if ($f == '.' || $f == '..') continue;
    $fpath = "$d/$f";
    if (is_dir($fpath)) {
      if (!is_link($fpath)) array_push($dirs,$fpath);
      continue;
    }
    if (!is_file($fpath)) continue;
    if (preg_match('/\.php$/',$f) || preg_match('/\.txt$/',$f)) {
      echo("- $fpath\n");
      $p[$fpath] = file_get_contents($fpath);
    }
  }
  closedir($dh);
}
$p->compressFiles(Phar::GZ);

//Stop buffering write requests to the Phar archive, and save changes to disk
$p->stopBuffering();
//echo "my.phar archive has been saved";
