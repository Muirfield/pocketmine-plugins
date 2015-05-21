<?php
/**
 ** OVERVIEW:Basic Usage
 **
 ** COMMANDS
 **
 ** * ls : List info on world protection.
 **   usage: /wp **ls** _[world]_
 **   - /wp **ls**
 **     - shows an overview of protections applied to all loaded worlds
 **   - /wp **ls** _[world]_
 **     - shows details of an specific world
 **/

namespace aliuly\worldprotect;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;

class WpList extends BaseWp {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableSCmd("ls",["usage" => "[world]",
										"help" => "Show protections on worlds",
										"permission" => "wm.cmd.info",
										"aliases" => ["info"]]);
	}
	public function onSCommand(CommandSender $c,Command $cc,$scmd,$world,array $args) {
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$cm = $this->owner->getScmdMap();
		$pageNumber = $this->getPageNumber($args);

		if (count($args)==1) return $this->wpDetails($c,$args[0],$pageNumber);
		if (count($args)==0) return $this->wpList($c,$pageNumber);
		return false;
	}
	private function wpDetails(CommandSender $c,$world,$pageNumber) {
		if (!$this->owner->getServer()->isLevelGenerated($world)) {
			$c->sendMessage("World $world does not exist");
			return;
		}
		$f = $this->owner->getServer()->getDataPath(). "worlds/$world/wpcfg.yml";
		if (!is_file($f)) {
			$c->sendMessage("World $world is not protected");
			return;
		}
		$wcfg=(new Config($f,Config::YAML))->getAll();
		$txt = ["Details for $world"];
		if (isset($wcfg["protect"]))
			$txt[] = TextFormat::AQUA."Protect:  ".
					 TextFormat::WHITE.$wcfg["protect"];

		if (isset($wcfg["max-players"]))
			$txt[] = TextFormat::AQUA."Max Players:  ".
					 TextFormat::WHITE.$wcfg["max-players"];
		if (isset($wcfg["gamemode"]))
			$txt[] = TextFormat::AQUA."Gamemode:  ".
					 TextFormat::WHITE.$this->owner->gamemodeString($wcfg["gamemode"]);
		if (isset($wcfg["pvp"])) {
			if ($wcfg["pvp"] === true) {
				$txt[] = TextFormat::AQUA."PvP: ".TextFormat::RED."on";
			} elseif ($wcfg["pvp"] === false) {
				$txt[] = TextFormat::AQUA."PvP: ".TextFormat::GREEN."off";
			} else {
				$txt[] = TextFormat::AQUA."PvP: ".TextFormat::YELLOW."spawn-off";
			}
		}
		if (isset($wcfg["no-explode"])) {
			if ($wcfg["no-explode"] === "off") {
				$txt[] = TextFormat::AQUA."NoExplode: ".TextFormat::RED."off";
			} elseif ($wcfg["no-explode"] === "world") {
				$txt[] = TextFormat::AQUA."NoExplode: ".TextFormat::GREEN."world";
			} else {
				$txt[] = TextFormat::AQUA."NoExplode: ".TextFormat::YELLOW."spawn";
			}
		}
		if (isset($wcfg["border"]))
			$txt[] = TextFormat::AQUA."Border: ".TextFormat::WHITE.
					 implode(",",$wcfg["border"]);
		if (isset($wcfg["auth"]))
			$txt[] = TextFormat::AQUA."Auth List(".count($wcfg["auth"]).
					 "): ".TextFormat::WHITE.implode(",",$wcfg["auth"]);
		if (isset($wcfg["unbreakable"]))
			$txt[] = TextFormat::AQUA."Unbreakable(".count($wcfg["unbreakable"]).
					 ": ".TextFormat::WHITE.implode(",",$wcfg["unbreakable"]);

		if (isset($wcfg["motd"])) {
			$txt[] = "MOTD:";
			if (is_array($wcfg["motd"])) {
				foreach ($wcfg["motd"] as $ln) {
					$txt[] = TextFormat::BLUE."  ".$ln.TextFormat::RESET;
				}
			} else {
				$txt[] = TextFormat::BLUE."  ".$wcfg["motd"];
			}
		}
		return $this->paginateText($c,$pageNumber,$txt);
	}
	private function attrList($wcfg) {
		$attr = [];
		if (isset($wcfg["motd"])) {
			$attr[] = "motd";
		}
		if (isset($wcfg["protect"])) $attr[] = $wcfg["protect"];
		if (isset($wcfg["pvp"])) {
			if ($wcfg["pvp"] === true) {
				$attr[] = "pvp:on";
			} elseif ($wcfg["pvp"] === false) {
				$attr[] = "pvp:off";
			} else {
				$attr[] = "pvp:spawn-off";
			}
		}
		if (isset($wcfg["no-explode"]))
			$attr[] = "notnt:".$wcfg["no-explode"];
		if (isset($wcfg["border"])) $attr[] = "border";
		if (isset($wcfg["auth"]))
			$attr[] = "auth(".count($wcfg["auth"]).")";
		if (isset($wcfg["max-players"]))
			$attr[]="max:".$wcfg["max-players"];
		if (isset($wcfg["gamemode"]))
			$attr[]="gm:".$wcfg["gamemode"];
		if (isset($wcfg["unbreakable"]))
			$attr[]="ubab:".count($wcfg["unbreakable"]);

		return $attr;
	}

	private function wpList(CommandSender $c,$pageNumber) {
		$dir = $this->owner->getServer()->getDataPath(). "worlds/";
		if (!is_dir($dir)) {
			$c->sendMessage("[WP] Missing path $dir");
			return true;
		}
		$txt = [];
		$dh = opendir($dir);
		if (!$dh) return false;
		$cnt = 0;
		while (($world = readdir($dh)) !== false) {
			if ($world == '.' || $world == '..') continue;
			if (!$this->owner->getServer()->isLevelGenerated($world)) continue;
			$f = "$dir$world/wpcfg.yml";
			if (is_file($f)) {
				$attrs=$this->attrList((new Config($f,Config::YAML))->getAll());
			}
			$ln = "- ".TextFormat::YELLOW.$world;
			if (count($attrs)) {
				$ln .= TextFormat::AQUA." (".implode(", ",$attrs).")";
			}
			$txt[] = $ln;
			++$cnt;
		}
		if (!count($ln)) {
			$c->sendMessage("Nothing to report");
			return true;
		}
		array_unshift($txt,"Worlds: $cnt");
		return $this->paginateText($c,$pageNumber,$txt);
	}

}
