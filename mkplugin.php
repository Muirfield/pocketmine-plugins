<?php
define('CMD',array_shift($argv));
error_reporting(E_ALL);

function usage() {
  die("Usage:\n\t".CMD." [-o outdir]  <src_directory>\n");
}
$path = ".";

if (isset($argv[0]) && $argv[0] == '-o') {
  array_shift($argv);
  $path = array_shift($argv);
  if (!isset($path)) die("Must specify output path\n");
  if (!is_dir($path)) die("$path: output directory not found\n");
}
$path = preg_replace('/\/*$/',"",$path).'/';

$plug = array_shift($argv);
if (!isset($plug)) usage();
$plug = preg_replace('/\/*$/',"",$plug).'/';

if (!is_dir($plug)) die("$plug: directory doesn't exist!\n");
if (!is_file($pluginYml = $plug."plugin.yml")) die("missing plugin manifest\n");
if (!is_dir($srcDir = $plug."src/")) die("Source folder not found\n");

$manifest = yaml_parse_file($pluginYml);
if (!isset($manifest["name"]) || !isset($manifest["version"])) {
  die("Incomplete plugin manifest\n");
}

$pharname = $manifest["name"]."_v".$manifest["version"].".phar";
$phar = new Phar($path.$pharname);
$phar->setStub('<?php __HALT_COMPILER();');
$phar->setSignatureAlgorithm(Phar::SHA1);
$phar->startBuffering();

echo("Adding sources...\n");
$cnt = 0;
foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($plug)) as $s){
  if (!is_file($s)) continue;
  $cnt++;
  $d = substr($s,strlen($plug));
  echo("  [$cnt] $d\n");
  $phar->addFile(realpath($s),$d);
}

echo("Compressing files...\n");
$phar->compressFiles(Phar::GZ);
$phar->stopBuffering();
echo ("Created: $path$pharname\n");
