<?php
namespace aliuly\livesigns;
use \pocketmine\scheduler\AsyncTask;
use \pocketmine\Server;
use aliuly\livesigns\fetcher\Text;
use aliuly\livesigns\fetcher\File;
use aliuly\livesigns\fetcher\Scriptlet;
use aliuly\livesigns\fetcher\Url;
use aliuly\livesigns\fetcher\Rss;
use aliuly\livesigns\fetcher\Tweet;
use aliuly\livesigns\fetcher\Query;
use aliuly\livesigns\fetcher\Motd;

class FetchTask extends AsyncTask {
	public $started;
	public $owner;
	public $re;
	protected $pkgs;
	protected $cf;
	public function __construct($plugin,$cfg,$pkgs) {
		$this->owner = $plugin->getName();
		$this->started = time();
		$this->pkgs = $pkgs;
		$this->cf = $cfg;
		if ($plugin->debug) $plugin->getLogger()->debug("FetchTask started.");
	}
	public static function fetchClass($type) {
		switch (strtolower($type)) {
			case "text":
				return Text::class;
			case "file":
				return File::class;
			case "php":
				return Scriptlet::class;
			case "url":
				return Url::class;
			case "rss":
				return Rss::class;
			case "twitter":
				return Tweet::class;
			case "query":
				return Query::class;
			case "motd":
			  return Motd::class;
		}
		return null;
	}

	private function fetchJob($dat) {
		foreach (["type","content"] as $tag) {
			if (!isset($dat[$tag])) return "No \"\$tag\" defined";
		}
		$fetcher = self::fetchClass($dat["type"]);
		if ($fetcher === null) return "Invalid type: ". $dat["type"];
		$content = $fetcher::fetch($dat,$this->cf);
		return $content;
	}

	public function onRun() {
		//$this->setResult(null);
		$this->re = json_encode(null);
		$restab = [];
		foreach ($this->pkgs as $job) {
			list($id,$dat) = $job;
			$res = $this->fetchJob($dat);
			if (is_array($res)) {
				$restab[$id] = [ "content" => $res ];
			} else {
				$restab[$id] = [ "error" => $res ];
			}
		}
		//$this->setResult($restab);
		$this->re = json_encode($restab);
	}
	public function onCompletion(Server $server) {
		$plugin = $server->getPluginManager()->getPlugin($this->owner);
		if ($plugin == null) {
			$server->getLogger()->error("Internal ERROR: ".
														 __METHOD__.",".__LINE__);
			return;
		}
		if (!$plugin->isEnabled()) return;
		//$res = $this->getResult();
		if(!is_string($this->re)) {
			$plugin->getLogger()->error("Error retrieving task results - no array in re");
			return;
			}


		$res = json_decode($this->re, true);
		if ($res == null) {
			$plugin->getLogger()->error("Error retrieving task results");
			return;
		}
		$done = [];
		foreach ($res as $id=>$rr) {
			if (isset($rr["error"])) {
				$plugin->getLogger()->error($id.": ".$rr["error"]);
			} else {
				$done[$id] = $rr["content"];
			}
		}
		$plugin->retrieveDone($done);
		if ($plugin->debug) $plugin->getLogger()->debug("FetchTask completed.");
	}
}
