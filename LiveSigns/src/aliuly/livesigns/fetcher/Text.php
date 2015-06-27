<?php
namespace aliuly\livesigns\fetcher;

abstract class Text extends SignFetcher {
	static public function fetch($dat,$cfg) {
		if (!is_array($dat["content"])) return [$dat["content"]];
		return $dat["content"];
	}
}
