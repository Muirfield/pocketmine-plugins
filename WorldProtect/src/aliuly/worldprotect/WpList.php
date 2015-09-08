<?php
//= cmd:ls,Sub_Commands
//: List info on world protection.
//> usage: /wp **ls** _[world]_
//>    - /wp **ls**
//:      - shows an overview of protections applied to all loaded worlds
//>    - /wp **ls** _[world]_
//:      - shows details of an specific world
namespace aliuly\worldprotect;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use aliuly\worldprotect\common\mc;
use aliuly\worldprotect\common\MPMU;

class WpList extends BaseWp {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableSCmd("ls",["usage" => mc::_("[world]"),
										"help" => mc::_("Show protections on worlds"),
										"permission" => "wp.cmd.info",
										"aliases" => ["info"]]);
	}
	public function onSCommand(CommandSender $c,Command $cc,$scmd,$world,array $args) {
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$pageNumber = $this->getPageNumber($args);

		if (count($args)==1) return $this->wpDetails($c,$args[0],$pageNumber);
		if (count($args)==0) return $this->wpList($c,$pageNumber);
		return false;
	}
	private function wpDetails(CommandSender $c,$world,$pageNumber) {
		if (!$this->owner->getServer()->isLevelGenerated($world)) {
			$c->sendMessage(mc::_("World %1% does not exist",$world));
			return;
		}
		$f = $this->owner->getServer()->getDataPath(). "worlds/$world/wpcfg.yml";
		if (!is_file($f)) {
			$c->sendMessage(mc::_("World %1% is not protected",$world));
			return;
		}
		$wcfg=(new Config($f,Config::YAML))->getAll();
		$txt = [mc::_("Details for %1%",$world)];
		if (isset($wcfg["protect"]))
			$txt[] = TextFormat::AQUA.mc::_("Protect:  ").
					 TextFormat::WHITE.$wcfg["protect"];

		if (isset($wcfg["max-players"]))
			$txt[] = TextFormat::AQUA.mc::_("Max Players:  ").
					 TextFormat::WHITE.$wcfg["max-players"];
		if (isset($wcfg["gamemode"]))
			$txt[] = TextFormat::AQUA.mc::_("Gamemode:  ").
					 TextFormat::WHITE.MPMU::gamemodeStr($wcfg["gamemode"]);

		if (isset($wcfg["pvp"])) {
			if ($wcfg["pvp"] === true) {
				$txt[] = TextFormat::AQUA.mc::_("PvP: ").TextFormat::RED.mc::_("on");
			} elseif ($wcfg["pvp"] === false) {
				$txt[] = TextFormat::AQUA.mc::_("PvP: ").TextFormat::GREEN.mc::_("off");
			} else {
				$txt[] = TextFormat::AQUA.mc::_("PvP: ").TextFormat::YELLOW.mc::_("spawn-off");
			}
		}
		if (isset($wcfg["no-explode"])) {
			if ($wcfg["no-explode"] === "off") {
				$txt[] = TextFormat::AQUA.mc::_("NoExplode: ").TextFormat::RED.mc::_("off");
			} elseif ($wcfg["no-explode"] === "world") {
				$txt[] = TextFormat::AQUA.mc::_("NoExplode: ").TextFormat::GREEN.mc::_("world");
			} else {
				$txt[] = TextFormat::AQUA.mc::_("NoExplode: ").TextFormat::YELLOW.mc::_("spawn");
			}
		}
		if (isset($wcfg["border"]))
			$txt[] = TextFormat::AQUA.mc::_("Border: ").TextFormat::WHITE.
					 implode(",",$wcfg["border"]);
		if (isset($wcfg["auth"]))
			$txt[] = TextFormat::AQUA.mc::_("Auth List(%1%): ",count($wcfg["auth"])).
					 TextFormat::WHITE.implode(",",$wcfg["auth"]);

		if (isset($wcfg["unbreakable"]))
			$txt[] = TextFormat::AQUA.mc::_("Unbreakable(%1%): ",count($wcfg["unbreakable"])).
					 TextFormat::WHITE.implode(",",$wcfg["unbreakable"]);
		if (isset($wcfg["bancmds"]))
			$txt[] = TextFormat::AQUA.mc::_("Ban Commands(%1%): ",count($wcfg["bancmds"])).
						TextFormat::WHITE.implode(",",$wcfg["bancmds"]);
		if (isset($wcfg["banitem"]))
			$txt[] = TextFormat::AQUA.mc::_("Banned(%1%): ",count($wcfg["banitem"])).
					 TextFormat::WHITE.implode(",",$wcfg["banitem"]);

		if (isset($wcfg["motd"])) {
			$txt[] = mc::_("MOTD:");
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
			$attr[] = mc::_("motd");
		}
		if (isset($wcfg["protect"])) $attr[] = $wcfg["protect"];
		if (isset($wcfg["pvp"])) {
			if ($wcfg["pvp"] === true) {
				$attr[] = mc::_("pvp:on");
			} elseif ($wcfg["pvp"] === false) {
				$attr[] = mc::_("pvp:off");
			} else {
				$attr[] = mc::_("pvp:spawn-off");
			}
		}
		if (isset($wcfg["no-explode"]))
			$attr[] = mc::_("notnt:").$wcfg["no-explode"];
		if (isset($wcfg["border"])) $attr[] = mc::_("border");
		if (isset($wcfg["auth"]))
			$attr[] = mc::_("auth(%1%)",count($wcfg["auth"]));
		if (isset($wcfg["max-players"]))
			$attr[]=mc::_("max:").$wcfg["max-players"];
		if (isset($wcfg["gamemode"]))
			$attr[]=mc::_("gm:").$wcfg["gamemode"];
		if (isset($wcfg["unbreakable"]))
			$attr[]=mc::_("ubab:").count($wcfg["unbreakable"]);
		if (isset($wcfg["bancmds"]))
				$attr[]=mc::_("bc:").count($wcfg["bancmds"]);
		if (isset($wcfg["banitem"]))
			$attr[]=mc::_("bi:").count($wcfg["banitem"]);
		return $attr;
	}

	private function wpList(CommandSender $c,$pageNumber) {
		$dir = $this->owner->getServer()->getDataPath(). "worlds/";
		if (!is_dir($dir)) {
			$c->sendMessage(mc::_("[WP] Missing path %1%",$dir));
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
			} else {
				$attrs = [];
			}
			$ln = "- ".TextFormat::YELLOW.$world;
			if (count($attrs)) {
				$ln .= TextFormat::AQUA." (".implode(", ",$attrs).")";
			}
			$txt[] = $ln;
			++$cnt;
		}
		if (!count($ln)) {
			$c->sendMessage(mc::_("Nothing to report"));
			return true;
		}
		array_unshift($txt,mc::_("Worlds: %1%",$cnt));
		return $this->paginateText($c,$pageNumber,$txt);
	}

}
