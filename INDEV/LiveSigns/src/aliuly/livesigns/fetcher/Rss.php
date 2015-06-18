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
		if (preg_match('/^(\d+):(\d+)$/',$item,$mv)) {
			$txt = "";
			$step = $mv[1] < $mv[2] ? 1 : -1;
			for ($item = $mv[1]; $item <= $mv[2]; $item += $step) {
				if (!isset($rs["items"][$item])) {
					if (strlen($txt) == 0) return "RSS no item index $item";
					continue;
				}
				if (!isset($rs["items"][$item][$selector])) {
					if (strlen($txt) == 0) return "RSS($item) has no tag $selector";
					continue;
				}
				if (strlen($txt)) $txt.="\n";
				$txt .= $rs["items"][$item][$selector];
			}
		} else {
			if (!isset($rs["items"][$item])) return "RSS no item index $item";
			if (!isset($rs["items"][$item][$selector]))
				return "RSS($item) has no tag $selector";
			$txt =$rs["items"][$item][$selector];
		}
		return explode("\n",$txt);
	}
}
