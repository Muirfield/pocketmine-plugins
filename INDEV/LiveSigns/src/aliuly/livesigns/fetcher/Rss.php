<?php
namespace aliuly\livesigns\fetcher;
use pocketmine\utils\Utils;
use aliuly\livesigns\lastRSS;

abstract class Rss extends SignFetcher {
	static public function fetch($dat) {
		$url = "";
		$item = 0;
		$selector = "title";
		if (is_array($dat["content"])) {
			if (isset($dat["content"][0])) $url = $dat["content"][0];
			if (isset($dat["content"][1])) $item = $dat["content"][1];
			if (isset($dat["content"][2])) $selector = $dat["content"][2];
		} else {
			$url = $dat["content"];
		}
		$rss = new lastRSS;
		$rss->cache_dir = '';
		$rss->cache_time = 0;
		$rss->cp = '';
		$rss->date_format = 'l';
		$rs = $rss->get($url);
		if (!isset($rs["items"])) return "RSS returned no items";
		if (!isset($rs["items"][$item])) return "RSS no item index $item";
		if (!isset($rs["items"][$item][$selector]))
			return "RSS($item) has no tag $selector";
		return explode("\n",$rs["items"][$item][$selector]);
	}
}
