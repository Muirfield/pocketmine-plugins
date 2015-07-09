<?php
namespace aliuly\livesigns\fetcher;
use aliuly\common\GetMotd;


abstract class Motd extends SignFetcher {
	static public function fetch($dat,$cfg) {
		if (!is_array($dat["content"])) {
			$f = $cfg["path"].$dat["content"];
			if (is_file($f)) {
				$txt = file($f,FILE_IGNORE_NEW_LINES);
				if ($txt === false) $txt = "Error reading file";
			} else {
				$txt = [$dat["content"]];
			}
		} else {
			$txt = $dat["content"];
		}
		$opts = explode(",",implode("\n",$txt),3);
		if (!isset($opts[0])) return "Query missing hostname";
		$host = $opts[0];
		$port = isset($opts[1]) ? $opts[1] : 19132; // Default port
		$msg = isset($opts[2]) ? $opts[2] : "{motd}";
		$info = GetMotd::query($host,$port, 1);
		if (!is_array($info)) return $info;

		$txt = [ $msg ];
		foreach ($info as $i=>$j) {
			$txt[] = $i."\t".$j;
		}
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
		//print_r($vars);//##DEBUG
		return $txt;
	}
	static public function default_age() { return 4; }
}
