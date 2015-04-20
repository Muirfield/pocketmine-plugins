<?php
namespace aliuly\grabbag;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;

use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Server;

class Main extends PluginBase implements Listener {
	protected $state;
	protected $modules;

	public function onEnable(){
		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
		$defaults = [
			"commands" => [
				"players" => true,
				"ops" => true,
				"gm?" => true,
				"as" => true,
				"slay" => true,
				"heal" => true,
				"whois" => true,
				"mute-unmute" => true,
				"freeze-thaw" => true,

				"showtimings" => true,
				"seeinv-seearmor" => true,
				"clearinv" => true,
				"get" => true,
				"shield" => true,
				"servicemode" => true,
				"opms" => true,
				"entitites" => true,
				"after-at" => true,
				"rpt" => true,
				"summon-dismiss" => true,
				"pushtp-poptp" => true,
				"prefix" => true,
			],
			"modules" => [
				"adminjoin" => true,
				"servermotd" => true,
				"repeater" => true,
			],
			"freeze-thaw" => [
				"hard-freeze"=>false,
			],
		];
		$cnt = 0;
		$cfg=(new Config($this->getDataFolder()."config.yml",
									  Config::YAML,$defaults))->getAll();
		if ($cfg["commands"]["players"]) {
			$cnt++;
			$this->modules[]= new CmdPlayers($this);
		}
		if ($cfg["commands"]["ops"]) {
			$cnt++;
			$this->modules[]= new CmdOps($this);
		}
		if ($cfg["commands"]["gm?"]) {
			$cnt++;
			$this->modules[]= new CmdGmx($this);
		}
		if ($cfg["commands"]["as"]) {
			$cnt++;
			$this->modules[]= new CmdAs($this);
		}
		if ($cfg["commands"]["slay"]) {
			$cnt++;
			$this->modules[]= new CmdSlay($this);
		}
		if ($cfg["commands"]["heal"]) {
			$cnt++;
			$this->modules[]= new CmdHeal($this);
		}
		if ($cfg["commands"]["whois"]) {
			$cnt++;
			$this->modules[]= new CmdWhois($this);
		}
		if ($cfg["commands"]["freeze-thaw"]) {
			$cnt++;
			$this->modules[]= new CmdFreezeMgr($this,$cfg["freeze-thaw"]["hard-freeze"]);
		}
		if ($cfg["commands"]["mute-unmute"]) {
			$cnt++;
			$this->modules[]= new CmdMuteMgr($this);
		}
		if ($cnt) {
			$this->state = [];
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
		}
		$this->getLogger()->info("enabled $cnt modules");
	}
	public function onPlayerQuit(PlayerQuitEvent $ev) {
		$n = strtolower($ev->getPlayer()->getName());
		if (isset($this->state[$n])) unset($this->state[$n]);
	}
	public function getState($label,$player,$default) {
		if ($player instanceof CommandSender) $player = $player->getName();
		$player = strtolower($player);
		if (!isset($this->state[$player])) return $default;
		if (!isset($this->state[$player][$label])) return $default;
		return $this->state[$player][$label];
	}
	public function setState($label,$player,$val) {
		if ($player instanceof CommandSender) $player = $player->getName();
		$player = strtolower($player);
		if (!isset($this->state[$player])) $this->state[$player] = [];
		$this->state[$player][$label] = $val;
	}
	public function unsetState($label,$player) {
		if ($player instanceof CommandSender) $player = $player->getName();
		$player = strtolower($player);
		if (!isset($this->state[$player])) return;
		if (!isset($this->state[$player][$label])) return;
		unset($this->state[$player][$label]);
	}

	public function gamemodeString($mode) {
		// For the moment we do this... When PM1.5 hits GA, we change
		// to proper localized strings.
		$t = Server::getGamemodeString($mode);
		if (substr($t,0,strlen("%gamemode.")) == "%gameMode.") {
			$t = substr($t,strlen("%gamemode."));
		}
		return ucfirst(strtolower($t));
	}
}
