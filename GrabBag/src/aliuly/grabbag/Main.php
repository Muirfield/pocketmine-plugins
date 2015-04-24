<?php
namespace aliuly\grabbag;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerQuitEvent;

class Main extends PluginBase implements Listener {
	protected $state;
	protected $modules;

	public function onEnable(){
		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
		$defaults = [
			"commands" => [
				"players" => "CmdPlayers",
				"ops" => "CmdOps",
				"gm?" => "CmdGmx",
				"as" => "CmdAs",
				"slay" => "CmdSlay",
				"heal" => "CmdHeal",
				"whois" => "CmdWhois",
				"mute-unmute" => "CmdMuteMgr",
				"freeze-thaw" => "CmdFreezeMgr",
				"showtimings" => "CmdTimings",
				"seeinv-seearmor" => "CmdShowInv",
				"clearinv" => "CmdClearInv",
				"get" => "CmdGet",
				"shield" => "CmdShieldMgr",
				"srvmode" => "CmdSrvModeMgr",
				"opms-rpt" => "CmdOpMsg",
				"entities" => "CmdEntities",
				"after-at" => "CmdAfterAt",
				"summon-dismiss" => "CmdSummon",
				"pushtp-poptp" => "CmdTpStack",
				"prefix" => "CmdPrefixMgr",
				"spawn" => "CmdSpawn",
				"burn" => "CmdBurn",
				"throw" => "CmdThrow",
				"blowup" => "CmdBlowUp",
				"setarmor" => "CmdSetArmor",
				"spectator"=> "CmdSpectator",
				"followers"=> "CmdFollowMgr",
				"rcon-client" => "CmdRcon",
			],
			"modules" => [
				"joins" => "JoinMgr",
				"repeater" => "RepeatMgr"
			],
			"joins" => [
				"adminjoin" => true,
				"servermotd" => true,
			],
			"freeze-thaw" => [
				"hard-freeze"=>false,
			],
		];
		$cfg=(new Config($this->getDataFolder()."config.yml",
									  Config::YAML,$defaults))->getAll();
		if (!isset($cfg["rcon-client"])) $cfg["rcon-client"] = [];
		$this->modules = [];
		foreach(["commands","modules"] as $type) {
			//print_r($cfg[$type]);//##DEBUG
			foreach($cfg[$type] as $name=>$class) {
				if(strpos($class,"\\") === false)
					$class = __NAMESPACE__."\\".$class;
				//echo "$class\n";//##DEBUG
				if (isset($cfg[$name]))
					$this->modules[] = new $class($this,$cfg[$name]);
				else
					$this->modules[] = new $class($this);
			}
		}

		if (count($this->modules)) {
			$this->state = [];
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
		}
		$this->getLogger()->info("enabled ".count($this->modules)." modules");
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
		switch($mode) {
			case 0: return "Survival";
			case 1: return "Creative";
			case 2: return "Adventure";
		}
		return "$mode-mode";
	}
}
