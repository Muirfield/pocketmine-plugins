<?php
//= cmd:near,Informational
//: Shows what players are near by
//> usage: **near** _[radius]_

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

//use pocketmine\Player;
//use pocketmine\utils\TextFormat;
use aliuly\common\BasicCli;
use aliuly\common\mc;
use aliuly\common\MPMU;
use aliuly\common\PermUtils;


class CmdPlayers extends BasicCli implements CommandExecutor {

	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.near", "near by players", "true");
		$this->enableCmd("near",
							  ["description" => mc::_("show near by players"),
								"usage" => mc::_("/near [radius]"),
								"permission" => "gb.cmd.near"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "near") return false;
		if (!MPMU::inGame($sender)) return true;
		switch (count($args)) {
			case 0:
				$radius = 64*64;
				break;
			case 1:
				$radius = ((int)$args[0])*((int)$args[0]);
				break;
			default:
				return false;
		}

		$players = [];

		foreach ($sender->getLevel()->getPlayers() as $pl) {
			$dist = $pl->distanceSquared($players);
			if ($dist < $radius) $players[] = $pl->getDisplayName();
		}
		if (count($players) == 0) {
			$sender->sendMessage(mc::_("No near by players found!"));
		} else {
			$sender->sendMessage(mc::_("Neighbors(%1%): %2%", count($players), implode(", ",$players)));
		}
		return true;
	}
}
