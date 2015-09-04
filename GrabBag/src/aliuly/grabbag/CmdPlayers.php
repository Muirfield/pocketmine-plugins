<?php
//= cmd:players,Informational
//: Shows what players are on-line
//> usage: **players**

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\Player;
use pocketmine\utils\TextFormat;
use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;
use aliuly\grabbag\common\PermUtils;


class CmdPlayers extends BasicCli implements CommandExecutor {

	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.players", "connected players", "true");
		$this->enableCmd("players",
							  ["description" => mc::_("show players connected and locations"),
								"usage" => mc::_("/players"),
								"aliases" => ["who"],
								"permission" => "gb.cmd.players"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "players") return false;

		$tab = [[ mc::_("Player"),mc::_("World"),mc::_("Pos"),mc::_("Health"),mc::_("Mode") ]];
		$cnt = 0;
		foreach ($this->owner->getServer()->getOnlinePlayers() as $player) {
			if(!$player->isOnline() ||
				(($sender instanceof Player) && !$sender->canSee($player))) continue;
			$pos = $player->getPosition();
			$j = count($tab);
			$mode = substr(MPMU::gamemodeStr($player->getGamemode()),0,4);
			$tab[]=[$player->getName(),$player->getLevel()->getName(),
					  $pos->getFloorX().",".$pos->getFloorY().",".$pos->getFloorZ(),
					  intval($player->getHealth()).'/'.intval($player->getMaxHealth()),
					  $mode];
			++$cnt;
		}
		if (!$cnt) {
			$sender->sendMessage(TextFormat::RED.mc::_("Nobody is on-line at the moment"));
			return true;
		}
		$tab[0][0] = mc::_("Players: %1%",$cnt);
		$pageNumber = $this->getPageNumber($args);
		return $this->paginateTable($sender,$pageNumber,$tab);
	}
}
