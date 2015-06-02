<?php
/**
 ** OVERVIEW:Player Management
 **
 ** COMMANDS
 **
 ** * gmc : Change your gamemode to _Creative_.
 **   usage: **gmc**
 ** * gms : Change your gamemode to _Survival_.
 **   usage: **gms**
 ** * gma : Change your gamemode to _Adventure_.
 **   usage: **gma**
 ** * gmspc : Change your gamemode to _Spectator_.
 **   usage: **gmspc**
 **
 **/
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\Player;
use pocketmine\utils\TextFormat;
use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;

class CmdGmx extends BasicCli implements CommandExecutor {

	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("gmc",
							  ["description" => mc::_("switch gamemode to creative"),
								"usage" => mc::_("/gmc"),
								"permission" => "gb.cmd.gmc"]);
		$this->enableCmd("gms",
							  ["description" => mc::_("switch gamemode to survival"),
								"usage" => mc::_("/gms"),
								"permission" => "gb.cmd.gms"]);
		$this->enableCmd("gma",
							  ["description" => mc::_("switch gamemode to adventure"),
								"usage" => mc::_("/gma"),
								"permission" => "gb.cmd.gma"]);
		$this->enableCmd("gmspc",
							  ["description" => mc::_("switch gamemode to spectator"),
								"usage" => mc::_("/gmspc"),
								"permission" => "gb.cmd.gmspc"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if (!MPMU::inGame($sender)) return true;
		switch($cmd->getName()) {
			case "gmc":
				$mode = 1;
				break;
			case "gms":
				$mode = 0;
				break;
			case "gma":
				$mode = 2;
				break;
			case "gmspc":
				$mode = 3;
				break;
			default:
				return false;
		}
		if ($mode !== $sender->getGamemode()) {
			$sender->setGamemode($mode);
			if ($mode !== $sender->getGamemode()) {
				$sender->sendMessage(TextFormat::RED.mc::_("Unable to change gamemode"));
			} else {
				$this->owner->getServer()->broadcastMessage(
					mc::_("%1% changed gamemode to %2% mode",
							$sender->getName(), MPMU::gamemodeStr($mode)));
			}
		} else {
			$sender->sendMessage(
				mc::_("You are already in %1% mode",MPMU::gamemodeStr($mode)));
		}
		return true;
	}
}
