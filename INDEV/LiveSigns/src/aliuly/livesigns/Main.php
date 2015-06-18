<?php
/**
 ** CONFIG:main
 **/
namespace aliuly\livesigns;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\event\Listener;
use pocketmine\utils\Config;

use aliuly\livesigns\common\PluginCallbackTask;
use aliuly\livesigns\common\BasicPlugin;
use aliuly\livesigns\common\BasicHelp;
use aliuly\livesigns\common\mc;

class Main extends BasicPlugin implements CommandExecutor {
	protected $texts;			// trigger texts
	protected $fetcher;		// async task retriever
	protected $fetch_redo;	// Re-do a fetch
	protected $signsCfg;		// user sign configuration
	protected $signsTxt;		// preprocessed sign text
	protected $cmds;			// Contains command implementations
	protected $fetchcfg;		// Fetcher Configuration
	protected $floats;		// Floating text handler

	public function onDisable() {
		if ($this->fetcher !== null && !$this->fetcher->isFinished()) {
			$id = $this->fetcher->getTaskId();
			$this->getServer()->getScheduler()->cancelTask($id);
		}
		parent::onDisable();
	}

	public function onEnable(){
		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
		mc::plugin_init($this,$this->getFile());

		$this->saveResource("signs.yml");
		$this->saveResource("welcome.txt");
		$this->saveResource("floats.yml");
		$this->saveResource("tops.php");

		$defaults = [
			"version" => $this->getDescription()->getVersion(),
			"# settings" => "tunnable parameters",
			"settings" => [
				"# tile-updates" => "How often to update signs in game from cache",// (in-ticks)
				"tile-updates" => 80,
				"# cache-signs" => "How long to cache sign data (seconds)",
				"cache-signs" => 7200,
				"# expire-cache" => "How often to expire caches (ticks)",
				"expire-cache" => 8000,
			],
			"fetcher" => [
				"# path" => "file path for the file fetcher",
				"# twitter" => "Used by the twitter feed fetcher",
				"twitter" => [
					'oauth_access_token' => "YOUR_OAUTH_ACCESS_TOKEN",
					'oauth_access_token_secret' => "YOUR_OAUTH_ACCESS_TOKEN_SECRET",
					'consumer_key' => "YOUR_CONSUMER_KEY",
					'consumer_secret' => "YOUR_CONSUMER_SECRET"
					],
			],
			"# signs" => "trigger text",
			"signs" => [
				"tag" => ["[LIVESIGN]","[livesign]"],
			],
		];
		$cf = (new Config($this->getDataFolder()."config.yml",
								Config::YAML, $defaults))->getAll();
		if (!isset($cf["fetcher"]["path"])) {
			$cf["fetcher"]["path"] = $this->getDataFolder();
		}
		$this->texts = [];
		foreach ($cf["signs"] as $a=>&$b) {
			foreach ($b as $c) {
				$this->texts[$c] = $a;
			}
		}

		$this->signsCfg = [];
		$this->signsTxt = [];
		$this->loadSigns();
		$this->fetcher = null;
		$this->fetchcfg = $cf["fetcher"];

		$this->getServer()->getScheduler()->scheduleRepeatingTask(
			new PluginCallbackTask($this,[$this,"expireCache"],[$cf["settings"]["cache-signs"]]),$cf["settings"]["expire-cache"]
		);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new TileUpdTask($this),$cf["settings"]["tile-updates"]);

		$this->floats = new ParticleTxt($this,$cf["settings"]["tile-updates"]);
		$this->cmds = [
			new LsCmds($this),
			"fs"=>new FsCmds($this),
			new BasicHelp($this),
		];
	}
	public function scheduleRetrieve() {
		if ($this->fetcher !== null && !$this->fetcher->isFinished()) {
			$this->fetch_redo = true;
			return;
		}
		$this->fetch_redo = false;
		$pkgs = [];
		foreach ($this->signsCfg as $id => $dat) {
			if (isset($this->signsTxt[$id])) {
				if (isset($this->signsTxt[$id]["datetime"])) continue; // No need to fetch this one... still current
			}
			$pkgs[] = [ $id, $dat ];
		}
		// Nothing to do...
		if (count($pkgs) == 0) return;
		$task = $this->fetcher = new FetchTask($this,$this->fetchcfg,$pkgs);

		$this->getServer()->getScheduler()->scheduleAsyncTask($task);
	}
	public function retrieveDone($pkgs) {
		$now = time();
		foreach ($pkgs as $id=>$txt) {
			$this->signsTxt[$id] = [ "text"=> $txt, "datetime" => $now ];
		}
		$this->fetcher = null;
		if ($this->fetch_redo) $this->scheduleRetrieve();
	}
	public function expireCache($maxage) {
		$now = time();
		foreach (array_keys($this->signsTxt) as $id) {
			if (!isset($this->signsCfg[$id])) {
				// Removed...
				unset($this->signsTxt[$id]);
				continue;
			}
			if (!isset($this->signsTxt[$id]["datetime"])) continue;
			if (($this->signsTxt[$id]["datetime"] + $maxage) < $now) unset($this->signsTxt[$id]["datetime"]);
		}
		$this->scheduleRetrieve();
	}
	private function getText($id) {
		if (isset($this->signsCfg[$id]) && isset($this->signsCfg[$id]["type"]) &&
			 strtolower($this->signsCfg[$id]["type"]) == "php") {
			// It is PHP script!
			$plugin = $this;
			$server = $this->getServer();
			$logger = $this->getLogger();
			ob_start();
			eval("?>".implode("\n",$this->signsTxt[$id]["text"]));
			//$t = ob_get_clean();
			//echo $t;//##DEBUG
			//return explode("\n",$t);
			return  explode("\n",ob_get_clean());
		}
		return $this->signsTxt[$id]["text"];
	}
	public function getLiveText($id,$opts) {
		if (!isset($this->signsTxt[$id])) return null;
		if ($opts == null) {
			// Default is to do nothing,
			return $this->getText($id);
		}
		$opts = ",$opts,";
		$width = 75; $wrapper = "wwrap";
		if (preg_match('/,width=(\d+),/',$opts,$mv)) {
			$width = $mv[1];
		}
		foreach (['/,word,/i' => "wwrap",
					 '/,char,/i' => "wrap"] as $re=>$mode) {
			if (preg_match($re,$opts)) $wrapper = $mode;
		}
		$stx = $this->getText($id);
		$stx  = explode("\n",TextWrapper::$wrapper(implode("\n",$stx),$width));
		return $stx;
	}
	public function getLiveSign($sign) {
		if (!isset($this->texts[$sign[0]])) return null;
		$id = trim($sign[1]);
		if (!isset($this->signsTxt[$id])) return null;

		// We fold lines by default, unless
		// line4 has - raw or word

		switch (strtolower($sign[3])) {
			case "raw":
			case "none":
				$stx = $this->getText($id);
				break;
			case "word":
				$stx = explode("\n",TextWrapper::wwrap(implode("\n",$this->getText($id))));
				break;
			default:
				$stx = explode("\n",TextWrapper::wrap(implode("\n",$this->getText($id))));
				break;
		}

		$text = [ "","","","" ];
		$i = 0; $s = 1; $j = 0;
		if (preg_match('/^\s*(\d+):(\d+)\s*$/',$sign[2],$mv)) {
			$i = $mv[1];
			$s = $mv[2];
			if ($s < 1) $s = 1;
		}
		while ($i < count($stx) && $j < 4) {
			$text[$j++] = $stx[$i];
			$i += $s;
		}
		return $text;
	}
	public function loadSigns() {
		$path = $this->getDataFolder()."signs.yml";
		if (!file_exists($path)) {
			// No sign data found!
			$this->signsCfg = [];
			return;
		}
		$cf = (new Config($path,Config::YAML))->getAll();
		$this->signsCfg = $cf;
	}
	public function saveSigns() {
		$path = $this->getDataFolder()."signs.yml";
		$yml = new Config($path,Config::YAML,[]);
		$yml->setAll($this->signsCfg);
		$yml->save();
	}

	//////////////////////////////////////////////////////////////////////
	//
	// Command implementations
	//
	//////////////////////////////////////////////////////////////////////
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
		if ($cmd->getName() == "floatsigns") {
			return $this->cmds["fs"]->onSCmd($sender,$args);
		}
		if ($cmd->getName() != "livesigns") return false;
		if (count($args) == 0) return false;
		return $this->dispatchSCmd($sender,$cmd,$args);
	}
	public function getSignCfg() {
		return $this->signsCfg;
	}
	public function getSignTxt() {
		return $this->signsTxt;
	}
	public function expireSign($id) {
		if ($id !== null) {
			if (!isset($this->signsTxt[$id])) return;
			if (!isset($this->signsTxt[$id]["datetime"])) return;
			unset($this->signsTxt[$id]["datetime"]);
			return;
		}
		foreach (array_keys($this->signsTxt) as $id) {
			$this->expireSign($id);
		}
	}
	public function updateSignCfg($id,$type,$content) {
		if ($type == null) {
			if (!isset($this->signsCfg[$id])) return;
			unset($this->signsCfg[$id]);
		} else {
			$this->signsCfg[$id] = [ "type" => $type, "content" => $content ];
		}
	}
	public function getStats() {
		$txt = [];
		if ($this->fetcher === null) {
			$txt[] = mc::_("Fetcher not running");
		} else {
			$txt[] = mc::_("Fetcher available: %1%",$this->fetcher->getTaskId());
			if ($this->fetcher->isFinished()) {
				$txt[] = mc::_("- Fetcher Finished");
			}
		}
		return $txt;
	}
	public function getFloats() { return $this->floats; }

}
