<?php
error_reporting(E_ALL);
define('LIBDIR',dirname(realpath(__FILE__))."/");
require_once(LIBDIR."maker/Spyc.php");

if (!is_file(SRCDIR."plugin.yml")) die("Missing plugin.yml");
$plugin = Spyc::YAMLLoad(SRCDIR."plugin.yml");
