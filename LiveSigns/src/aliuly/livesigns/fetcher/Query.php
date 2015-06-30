<?php
namespace aliuly\livesigns\fetcher;

abstract class Query extends SignFetcher {
	static public function fetch($dat,$cfg) {
		if (is_array($dat["content"]))  return $dat["content"];
		$f = $cfg["path"].$dat["content"];
		if (!is_file($f)) return [$dat["content"]];
		$txt = file($f,FILE_IGNORE_NEW_LINES);
		if ($txt === false) $txt = "Error reading file";
		return $txt;
	}
}
