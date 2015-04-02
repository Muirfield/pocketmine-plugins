<?php
if (isset($argv[0])) array_shift($argv); // Always strip this one...
define('CLASSLIB_DIR','phar://pmimporter.phar/classlib/');

if (!isset($argv[0])) {
  echo("No sub-command specified\n");
  require_once(CLASSLIB_DIR.'autoload.php');
  die();
}

if (in_array($argv[0],array("version","plugin","readme","help"))) {
  require_once("phar://pmimporter.phar/scripts/$argv[0].php");
  require_once(CLASSLIB_DIR.'autoload.php');
  exit;
}
require_once(CLASSLIB_DIR.'autoload.php');

if (is_readable("phar://pmimporter.phar/scripts/$argv[0].php")) {
  require_once("phar://pmimporter.phar/scripts/$argv[0].php");
} else {
  die("Unknown sub-command $argv[0].  Use \"help\"\n");
}
