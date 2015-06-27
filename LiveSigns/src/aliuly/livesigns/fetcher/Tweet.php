<?php
namespace aliuly\livesigns\fetcher;
use pocketmine\utils\Utils;
use aliuly\livesigns\TwitterAPIExchange;

abstract class Tweet extends SignFetcher {
	static public function fetch($dat,$cfg) {
		$tag = "";
		$item = 0;
		if (is_array($dat["content"])) {
			if (isset($dat["content"][0])) $tag = $dat["content"][0];
			if (isset($dat["content"][1])) $item = $dat["content"][1];
		} else {
			$tag = $dat["content"];
		}
		$url = "https://api.twitter.com/1.1/statuses/user_timeline.json";
		$requestMethod = "GET";
		$getfield = '?screen_name='.urlencode($tag).'&count='.($item+1);
		$twitter = new TwitterAPIExchange($cfg["twitter"]);
		$js = json_decode($twitter->setGetfield($getfield)
								->buildOauth($url, $requestMethod)
								->performRequest(), $assoc=true);
		if(isset($js["errors"][0]["message"])) return $js["errors"][0]["message"];
		if(!isset($js[$item])) return "No item index $item";
		if(!isset($js[$item]["text"])) return "No text for item $item";
		return explode("\n", $js[$item]["text"]);
	}
}
