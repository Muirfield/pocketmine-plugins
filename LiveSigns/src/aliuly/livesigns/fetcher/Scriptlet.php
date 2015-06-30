<?php
namespace aliuly\livesigns\fetcher;

abstract class Scriptlet extends SignFetcher {
	static public function fetch($dat,$cfg) {
		$txt = file($cfg["path"].$dat["content"],FILE_IGNORE_NEW_LINES);
		if ($txt === false) $txt = "Error reading file";
		return $txt;
	}
}
