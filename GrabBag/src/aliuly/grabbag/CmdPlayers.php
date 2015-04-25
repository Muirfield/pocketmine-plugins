<?php
/**
 ** OVERVIEW:Informational
 **
 ** COMMANDS
 **
 ** * players: Shows what players are on-line
 **   usage: **players**
 **/

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\Player;
use pocketmine\utils\TextFormat;

class CmdPlayers extends BaseCommand {

	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("players",
							  ["description" => "show players connected and locations",
								"usage" => "/players",
								"aliases" => ["who"],
								"permission" => "gb.cmd.players"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "players") return false;

		$tab = [[ "Player","World","Pos","Health","Mode" ]];
		$cnt = 0;
		foreach ($this->owner->getServer()->getOnlinePlayers() as $player) {
			if(!$player->isOnline() ||
				(($sender instanceof Player) && !$sender->canSee($player))) continue;
			$pos = $player->getPosition();
			$j = count($tab);
			$mode = substr($this->owner->gamemodeString($player->getGamemode()),0,4);
			$tab[]=[$player->getDisplayName(),$player->getLevel()->getName(),
					  $pos->getFloorX().",".$pos->getFloorY().",".$pos->getFloorZ(),
					  intval($player->getHealth()).'/'.intval($player->getMaxHealth()),
					  $mode];
			++$cnt;
		}
		if (!$cnt) {
			$sender->sendMessage(TextFormat::RED."Nobody is on-line at the moment");
			return true;
		}
		$tab[0][0] = "Players:$cnt";
		$pageNumber = $this->getPageNumber($args);
		return $this->paginateTable($sender,$pageNumber,$tab);
	}
}
