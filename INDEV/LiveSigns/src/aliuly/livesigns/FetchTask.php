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

class FetchTask extends AsyncTask {
	public $started;
	public $owner;
	protected $pkgs;
	protected $cf;
	public function __construct($plugin,$cfg,$pkgs) {
		$this->owner = $plugin->getName();
		$this->started = time();
		$this->pkgs = $pkgs;
		$this->cf = $cfg;
		$plugin->getLogger()->debug("FetchTask started.");
	}

	private function fetchJob($dat) {
		foreach (["type","content"] as $tag) {
			if (!isset($dat[$tag])) return "No \$tag\" defined";
		}
		switch (strtolower($dat["type"])) {
			case "text":
				$fetcher = Text::class;
				break;
			case "file":
				$fetcher = File::class;
				break;
			case "php":
				$fetcher = Scriptlet::class;
				break;
			case "url":
				$fetcher = Url::class;
				break;
			case "rss":
				$fetcher = Rss::class;
				break;
			case "twitter":
				$fetcher = Tweet::class;
				break;
			default:
				return "Invalid type: ". $dat["type"];
		}
		$content = $fetcher::fetch($dat,$this->cf);
		return $content;
	}

	public function onRun() {
		$this->setResult(null);
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
		$this->setResult($restab);
	}
	public function onCompletion(Server $server) {
		$plugin = $server->getPluginManager()->getPlugin($this->owner);
		if ($plugin == null) {
			$server->getLogger()->error("Internal ERROR: ".
														 __METHOD__.",".__LINE__);
			return;
		}
		if (!$plugin->isEnabled()) return;
		$res = $this->getResult();
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
		$plugin->getLogger()->debug("FetchTask completed.");
	}
}
