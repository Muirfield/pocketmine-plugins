<?php
namespace aliuly\livesigns\fetcher;

abstract class Scriptlet extends SignFetcher {
	static public function fetch($dat,$cfg) {
		$n = ",cached";
		$f = $cfg["path"].$dat["content"];
		if (substr($f,-(strlen($n))) == $n) {
			$cached = true;
			$f = subtr($f,0,strlen($f)-strlen($n));
		} else {
			$cached = false;
		}
		$txt = file($cfg["path"].$dat["content"],FILE_IGNORE_NEW_LINES);
		if ($txt === false || count($txt) == 0) $txt = "Error reading file";
		if ($cached) {
			$scriptlet = implode("\n",$txt);
			ob_start();
			eval($scriptlet);
			return "xx".explode("\n",ob_get_clean());
		} else {
			$txt[0] = "?>".$txt[0];
		}
		return $txt;
	}
}
