<?php
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\Player;
use pocketmine\utils\TextFormat;

class CmdGmx extends BaseCommand {

	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("gmc",
							  ["description" => "switch gamemode to creative",
								"usage" => "/gmc",
								"permission" => "gb.cmd.gmc"]);
		$this->enableCmd("gms",
							  ["description" => "switch gamemode to survival",
								"usage" => "/gms",
								"permission" => "gb.cmd.gms"]);
		$this->enableCmd("gma",
							  ["description" => "switch gamemode to adventure",
								"usage" => "/gma",
								"permission" => "gb.cmd.gma"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if (!$this->inGame($sender)) return true;
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
			default:
				return false;
		}
		if ($mode !== $sender->getGamemode()) {
			$sender->setGamemode($mode);
			if ($mode !== $sender->getGamemode()) {
				$sender->sendMessage(TextFormat::RED."Unable to change gamemode");
			} else {
				$this->owner->getServer()->broadcastMessage($sender->getName().
																		  " changed gamemode to ".
																		  $this->owner->gamemodeString($mode).
																		  " mode");
			}
		} else {
			$sender->sendMessage("You are alredy in ".
								 $this->owner->gamemodeString($mode).
								 " mode");
		}
		return true;
	}
}
