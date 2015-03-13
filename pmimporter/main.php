<?php
if (isset($argv[0])) array_shift($argv); // Always strip this one...
if (!isset($argv[0])) die("No sub-command specified\n");

define('CLASSLIB_DIR','phar://pmimporter.phar/classlib/');
require_once(CLASSLIB_DIR.'autoload.php');

if (is_readable("phar://pmimporter.phar/scripts/$argv[0].php")) {
  require_once("phar://pmimporter.phar/scripts/$argv[0].php");
} else {
  die("Unknown sub-command $argv[0]\n");
}
