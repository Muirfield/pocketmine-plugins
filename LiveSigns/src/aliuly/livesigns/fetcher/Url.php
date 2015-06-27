<?php
namespace aliuly\livesigns\fetcher;
use pocketmine\utils\Utils;

abstract class Url extends SignFetcher {
	static public function fetch($dat) {
		$txt = Utils::getURL($dat["content"]);
		$txt = html_entity_decode(strip_tags($txt),ENT_QUOTES|ENT_HTML401,"UTF-8");
		return explode("\n",$txt);
	}
}
