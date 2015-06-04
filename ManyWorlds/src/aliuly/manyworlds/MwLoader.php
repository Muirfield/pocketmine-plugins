<?php
/**
 ** OVERVIEW:Basic Usage
 **
 ** COMMANDS
 **
 ** * load : Loads a world
 **   usage: /mw **load** _<world>_
 **
 **   Loads _world_ directly.  Use _--all_ to load **all** worlds.
 **
 ** * unload : Unloads world
 **   usage: /mw **unload** _[-f]_  _<world>_
 **
 **   Unloads _world_.  Use _-f_ to force unloads.
 **/
namespace aliuly\manyworlds;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\utils\TextFormat;

use aliuly\manyworlds\common\mc;
use aliuly\manyworlds\common\MPMU;
use aliuly\manyworlds\common\BasicCli;

class MwLoader extends BasicCli {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableSCmd("load",["usage" => mc::_("<world|--all>"),
										"help" => mc::_("Load worlds"),
										"permission" => "mw.cmd.world.load",
										"aliases" => ["ld"]]);
		$this->enableSCmd("unload",["usage" => mc::_("[-f] <world>"),
										"help" => mc::_("Attempt to unload worlds"),
											 "permission" => "mw.cmd.world.load"]);
	}
	public function onSCommand(CommandSender $c,Command $cc,$scmd,$data,array $args) {
		if (count($args) == 0) return false;
		switch ($scmd) {
			case "load":
				return $this->mwWorldLoadCmd($c,implode(" ",$args));
			case "unload":
				$force = false;
				if ($args[0] == "-f") {
					$force = true;
					array_shift($args);
					if (count($args) == 0) return false;
				}
				return $this->mwWorldUnloadCmd($c,implode(" ",$args),$force);
		}
		return false;
	}
	private function mwWorldLoadCmd(CommandSender $sender,$wname) {
		if ($wname == "--all") {
			$wlst = [];
			foreach (glob($this->owner->getServer()->getDataPath(). "worlds/*") as $f) {
				$world = basename($f);
				if ($this->owner->getServer()->isLevelLoaded($world)) continue;
				if (!$this->owner->getServer()->isLevelGenerated($world)) continue;
				$wlst[] = $world;
			}
			if (count($wlst) == 0) {
				$sender->sendMessage(TextFormat::RED.
											mc::_("[MW] No levels to load"));
				return true;
			}
			$sender->sendMessage(
				TextFormat::AQUA.
				mc::n(
					mc::_("[MW] Loading one level"),
					mc::_("[MW] Loading ALL %1% levels",count($wlst)),
					count($wlst)));
		} else {
			if ($this->owner->getServer()->isLevelLoaded($wname)) {
				$sender->sendMessage(TextFormat::RED.
											mc::_("[MW] %1% already loaded",$wname));
				return true;
			}
			if (!$this->owner->getServer()->isLevelGenerated($wname)) {
				$sender->sendMessage(TextFormat::RED.
											mc::_("[MW] %1% does not exists",$wname));
				return true;
			}
			$wlst = [ $wname ];
		}
		foreach ($wlst as $world) {
			if (!$this->owner->autoLoad($sender,$world)) {
				$sender->sendMessage(TextFormat::RED.
											mc::_("[MW] Unable to load %1%",$world));
			}
		}
		return true;
	}
	private function mwWorldUnloadCmd(CommandSender $sender,$wname,$force) {
		if (MPMU::apiVersion("<1.12.0")) {
			// For old stuff...
			if ($wname == "--enable") {
				$this->owner->canUnload = true;
				$sender->sendMessage(TextFormat::YELLOW.
											mc::_("[MW] Unload sub-command enabled"));
				$sender->sendMessage(TextFormat::YELLOW.
											mc::_("[MW] To disable use: /mw unload --disable"));
				return true;
			}
			if ($wname == "--disable") {
				$this->owner->canUnload = false;
				$sender->sendMessage(TextFormat::GREEN.
											mc::_("[MW] Unload sub-command disabled"));
				$sender->sendMessage(TextFormat::GREEN.
											mc::_("[MW] To enable use: /mw unload --enable"));
				return true;
			}
			if (!$this->owner->canUnload) {
				$sender->sendMessage(TextFormat::RED.mc::_("[MW] Unload sub-command is disabled by default"));
				$sender->sendMessage(TextFormat::RED.mc::_("[MW] this is because that it usually causes the"));
				$sender->sendMessage(TextFormat::RED.mc::_("[MW] server to CRASH!"));
				$sender->sendMessage(TextFormat::RED.mc::_("[MW] To activate use:"));
				$sender->sendMessage(TextFormat::BLUE.mc::_("-   /mw unload --enable"));
				return true;
			}
		}
		// Actual implementation
		if (!$this->owner->getServer()->isLevelLoaded($wname)) {
			$sender->sendMessage(TextFormat::RED.mc::_("[MW] %1% is not loaded.",$wname));
			return true;
		}
		$level = $this->owner->getServer()->getLevelByName($wname);
		if ($level === null) {
			$sender->sendMessage(TextFormat::RED.mc::_("[MW] Unable to get %1%",$wname));
			return true;
		}
		if (!$this->owner->getServer()->unloadLevel($level,$force)) {
			if ($force)
				$sender->sendMessage(TextFormat::RED.mc::_("[MW] Unable to unload %1%",$wname));
			else
				$sender->sendMessage(TextFormat::RED.mc::_("[MW] Unable to unload %1%.  Try -f",$wname));
		} else {
			$sender->sendMessage(TextFormat::GREEN.mc::_("[MW] %1% unloaded.",$wname));
		}
		return true;
	}
}
