<?php
require("TextWrapper.php");
use aliuly\livesigns\TextWrapper;
$text = implode(" ",$argv);
echo $text."\n";
echo "---\n";
echo TextWrapper::wwrap($text)."\n";
echo "---\n";
echo TextWrapper::wrap($text)."\n";
