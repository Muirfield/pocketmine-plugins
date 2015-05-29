<?php
/**
 **
 ** CONFIG:features
 **
 ** This section you can enable/disable commands and listener modules.
 ** You do this in order to avoid conflicts between different
 ** PocketMine-MP plugins.  It has one line per feature:
 **
 **    feature: true|false
 **
 ** If `true` the feature is enabled.  if `false` the feature is disabled.
 **
 **/
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
		$mods = [
			"players" => [ "CmdPlayers", true ],
			"ops" => [ "CmdOps", true ],
			"gm?" => [ "CmdGmx", true ],
			"as" => [ "CmdAs", true ],
			"slay" => [ "CmdSlay", true ],
			"heal" => [ "CmdHeal", true ],
			"whois" => [ "CmdWhois", true ],
			"mute-unmute" => [ "CmdMuteMgr", true ],
			"freeze-thaw" => [ "CmdFreezeMgr", true ],
			"showtimings" => [ "CmdTimings", true ],
			"seeinv-seearmor" => [ "CmdShowInv", true ],
			"clearinv" => [ "CmdClearInv", true ],
			"get" => [ "CmdGet", true ],
			"shield" => [ "CmdShieldMgr", true ],
			"srvmode" => [ "CmdSrvModeMgr", true ],
			"opms-rpt" => [ "CmdOpMsg", true ],
			"entities" => [ "CmdEntities", true ],
			"after-at" => [ "CmdAfterAt", true ],
			"summon-dismiss" => [ "CmdSummon", true ],
			"pushtp-poptp" => [ "CmdTpStack", true ],
			"prefix" => [ "CmdPrefixMgr", true ],
			"spawn" => [ "CmdSpawn", true ],
			"burn" => [ "CmdBurn", true ],
			"throw" => [ "CmdThrow", true ],
			"blowup" => [ "CmdBlowUp", true ],
			"setarmor" => [ "CmdSetArmor", true ],
			"spectator"=> [ "CmdSpectator", true ],
			"followers"=> [ "CmdFollowMgr", true ],
			"rcon-client" => [ "CmdRcon", true ],
			"join-mgr" => [ "JoinMgr", true ],
			"repeater" => [ "RepeatMgr", true ],
			"broadcast-tp" => [ "BcTpMgr", false ],
			"fly" => [ "CmdFly", true ],
		];
		$defaults = [
			"version" => $this->getDescription()->getVersion(),
			"features" => [],
			"join-mgr" => [
				"adminjoin" => true,
				"servermotd" => true,
			],
			"broadcast-tp" => [
				"world" => true,
				"local" => 500,
			],
			"freeze-thaw" => [
				"hard-freeze"=>false,
			],
		];
		foreach ($mods as $i => $j) {
			$defaults["features"][$i] = $j[1];
		}
		$cfg=(new Config($this->getDataFolder()."config.yml",
									  Config::YAML,$defaults))->getAll();
		if (!isset($cfg["rcon-client"])) $cfg["rcon-client"] = [];
		$this->modules = [];
		foreach ($cfg["features"] as $i=>$j) {
			if (!isset($mods[$i])) {
				$this->getLogger()->notice("Unknown feature \"$i\" ignored.");
				continue;
			}
			if (!$j) continue;
			$class = $mods[$i][0];
			if(strpos($class,"\\") === false) $class = __NAMESPACE__."\\".$class;
			if (isset($cfg[$i]))
				$this->modules[$i] = new $class($this,$cfg[$i]);
			else
				$this->modules[$i] = new $class($this);
		}
		if (count($this->modules)) {
			$this->state = [];
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
			$this->getLogger()->info("enabled ".count($this->modules)." features");
		} else {
			$this->getLogger()->notice("NO features enabled");
		}
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
			case 3: return "Spectator";
		}
		return "$mode-mode";
	}
}
