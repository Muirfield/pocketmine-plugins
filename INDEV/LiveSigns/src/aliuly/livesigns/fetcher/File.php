<?php
namespace aliuly\livesigns\fetcher;

abstract class File extends SignFetcher {
	static public function fetch($dat,$cfg) {
		if (is_array($dat["content"])) {
			$txt = [];
			foreach ($dat["content"] as $f) {
				$ret = file($cfg["path"].$f,FILE_IGNORE_NEW_LINES);
				if ($ret === false) continue;
				array_push($txt,...$ret);
			}
			if (count($txt) == 0) $txt = "Error reading files";
		} else {
			$txt = file($cfg["path"].$dat["content"],FILE_IGNORE_NEW_LINES);
			if ($txt === false) $txt = "Error reading file";
		}
		return $txt;
	}
}
