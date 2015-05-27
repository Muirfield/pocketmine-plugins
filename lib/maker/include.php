<?php
define('LIBDIR',dirname(realpath(__FILE__))."/");
require_once(LIBDIR."Spyc.php");

if (!is_file(SRCDIR."plugin.yml")) die("Missing plugin.yml");
$plugin = Spyc::YAMLLoad(SRCDIR."plugin.yml");
