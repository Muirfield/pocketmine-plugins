<?php
/**
 ** OVERVIEW:Basic Usage
 **
 ** COMMANDS
 **
 ** * tp : Teleport to another world
 **   usage: /mw **tp** _[player]_ _<world>_
 **
 **   Teleports you to another world.  If _player_ is specified, that
 **   player will be teleported.
 **/
namespace aliuly\manyworlds;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\utils\TextFormat;

use aliuly\manyworlds\common\mc;
use aliuly\manyworlds\common\MPMU;
use aliuly\manyworlds\common\BasicCli;

class MwTp extends BasicCli {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableSCmd("tp",["usage" => mc::_("[player] <world>"),
										"help" => mc::_("Teleport across worlds"),
										"permission" => "mw.cmd.tp",
										"aliases" => ["teleport"]]);
	}
	public function onSCommand(CommandSender $c,Command $cc,$scmd,$data,array $args) {
		if (count($args) == 0) return false;
		$player = $c;
		if (count($args) > 1) {
			$player = $this->owner->getServer()->getPlayer($args[0]);
			if ($player !== null) {
				if (!MPMU::access($c,"mw.cmd.tp.others")) return true;
				array_shift($args);
			} else {
				// Compatibility with old versions...
				$player = $this->owner->getServer()->getPlayer($args[count($args)-1]);
				if ($player !== null) {
					if (!MPMU::access($c,"mw.cmd.tp.others")) return true;
					array_pop($args);
				} else {
					$player = $c;
				}
			}
		}
		if (!MPMU::inGame($player)) return true;
		$wname = implode(" ",$args);
		if ($player->getLevel() == $this->owner->getServer()->getLevelByName($wname)) {
			$c->sendMessage(
				$c == $player ?
				mc::_("You are already in %1%",$wname) :
				mc::_("%1% is already in %2%",$player->getName(),$wname));
			return true;
		}
		if (!$this->owner->autoLoad($c,$wname)) {
			$c->sendMessage(TextFormat::RED.mc::_("Teleport failed"));
			return true;
		}
		$level = $this->owner->getServer()->getLevelByName($wname);
		if ($level === null) {
			$c->sendMessage(TextFormat::RED.mc::_("Error GetLevelByName %1%"));
			return true;
		}
		if ($c != $player) {
			$player->sendMessage(TextFormat::YELLOW.mc::_("Teleporting you to %1% by %2%", $wname, $c->getName()));
		} else {
			$c->sendMessage(TextFormat::GREEN.mc::_("Teleporting to %1%",$wname));
		}
		$player->teleport($level->getSafeSpawn());
		return true;
	}
}
