<?php
/**
 ** OVERVIEW:Informational
 **
 ** COMMANDS
 **
 ** * players: Shows what players are on-line
 **   usage: **players**
 **
 ** * regcount: Shows how many players are registered.
 **   usage: **regcount**
 **
 ** * listreg: Shows registered players.
 **   usage: **listreg** _[pattern]_
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

class CmdPlayers extends BasicCli implements CommandExecutor {

	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("players",
							  ["description" => mc::_("show players connected and locations"),
								"usage" => mc::_("/players"),
								"aliases" => ["who"],
								"permission" => "gb.cmd.players"]);
		$this->enableCmd("regcount",
							  ["description" => mc::_("Show how many registered players are there"),
								"usage" => mc::_("/regcount"),
								"permission" => "gb.cmd.players"]);
		$this->enableCmd("listreg",
							  ["description" => mc::_("Show registered players"),
								"usage" => mc::_("/listreg [pattern]"),
								"permission" => "gb.cmd.players"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() == "playercount") {
			if (count($args) != 0) return false;
			$cnt = count(glob($this->owner->getServer()->getDataPath()."players/*.dat"));
			$sender->sendMessage(mc::n(mc::_("One player registered"),
												mc::_("%1% players registered",$cnt),
												$cnt));
			return true;
		}
		if ($cmd->getName() == "listreg") {
			$pageNumber = $this->getPageNumber($args);
			if (count($args) == 0) {
				$pattern = "*";
			} elseif (count($args) == 1) {
				$pattern = implode(" ",$args);
			} else {
				return false;
			}
			$f = glob($this->owner->getServer()->getDataPath()."players/".
						 $pattern.".dat");
			$txt = [ mc::n(mc::_("One player found"),
								  mc::_("%1% players found",count($f)),count($f)) ];
			$cols = 8;
			$i = 0;
			foreach ($f as $n) {
				$n = basename($n,".dat");
				if (($i++ % $cols) == 0) {
					$txt[] = $n;
				} else {
					$txt[count($txt)-1] .= ", ".$n;
				}
			}
			return $this->paginateText($sender,$pageNumber,$txt);
		}


		if ($cmd->getName() != "players") return false;

		$tab = [[ mc::_("Player"),mc::_("World"),mc::_("Pos"),mc::_("Health"),mc::_("Mode") ]];
		$cnt = 0;
		foreach ($this->owner->getServer()->getOnlinePlayers() as $player) {
			if(!$player->isOnline() ||
				(($sender instanceof Player) && !$sender->canSee($player))) continue;
			$pos = $player->getPosition();
			$j = count($tab);
			$mode = substr(MPMU::gamemodeStr($player->getGamemode()),0,4);
			$tab[]=[$player->getDisplayName(),$player->getLevel()->getName(),
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
