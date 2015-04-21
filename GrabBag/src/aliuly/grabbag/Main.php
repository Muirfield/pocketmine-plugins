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
				"srvmode" => true,
				"opms-rpt" => true,
				"entities" => true,
				"after-at" => true,
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
		$cfg=(new Config($this->getDataFolder()."config.yml",
									  Config::YAML,$defaults))->getAll();
		if ($cfg["commands"]["players"])
			$this->modules[]= new CmdPlayers($this);
		if ($cfg["commands"]["ops"])
			$this->modules[]= new CmdOps($this);
		if ($cfg["commands"]["gm?"])
			$this->modules[]= new CmdGmx($this);
		if ($cfg["commands"]["as"])
			$this->modules[]= new CmdAs($this);
		if ($cfg["commands"]["slay"])
			$this->modules[]= new CmdSlay($this);
		if ($cfg["commands"]["heal"])
			$this->modules[]= new CmdHeal($this);
		if ($cfg["commands"]["whois"])
			$this->modules[]= new CmdWhois($this);
		if ($cfg["commands"]["freeze-thaw"])
			$this->modules[]= new CmdFreezeMgr($this,$cfg["freeze-thaw"]["hard-freeze"]);
		if ($cfg["commands"]["mute-unmute"])
			$this->modules[]= new CmdMuteMgr($this);
		if ($cfg["commands"]["showtimings"])
			$this->modules[]= new CmdTimings($this);
		if ($cfg["commands"]["seeinv-seearmor"])
			$this->modules[]= new CmdShowInv($this);
		if ($cfg["commands"]["clearinv"])
			$this->modules[]= new CmdClearInv($this);
		if ($cfg["commands"]["get"])
			$this->modules[]= new CmdGet($this);
		if ($cfg["commands"]["shield"])
			$this->modules[]= new CmdShieldMgr($this);
		if ($cfg["commands"]["srvmode"])
			$this->modules[]= new CmdSrvModeMgr($this);
		if ($cfg["commands"]["opms-rpt"])
			$this->modules[]= new CmdOpMsg($this);
		if ($cfg["commands"]["entities"])
			$this->modules[]= new CmdEntities($this);
		if ($cfg["commands"]["after-at"])
			$this->modules[]= new CmdAfterAt($this);
		if ($cfg["commands"]["summon-dismiss"])
			$this->modules[]= new CmdSummon($this);
		if ($cfg["commands"]["pushtp-poptp"])
			$this->modules[]= new CmdTpStack($this);
		if ($cfg["commands"]["prefix"])
			$this->modules[]= new CmdPrefixMgr($this);

		if ($cfg["modules"]["repeater"])
			$this->modules[] = new RepeatMgr($this);
		if ($cfg["modules"]["adminjoin"] || $cfg["modules"]["servermotd"])
			$this->modules[] = new JoinMgr($this,
													 $cfg["modules"]["adminjoin"],
													 $cfg["modules"]["servermotd"]);

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
