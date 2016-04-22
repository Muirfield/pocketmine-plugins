<?php
/**
 ** OVERVIEW:Basic Usage
 **
 ** COMMANDS
 **
 ** * generators : List available world generators
 **   usage: /mw **generators**
 **
 **   List registered world generators.
 **/
namespace aliuly\manyworlds;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\utils\TextFormat;
use pocketmine\level\generator\Generator;

use aliuly\manyworlds\common\mc;
use aliuly\manyworlds\common\MPMU;
use aliuly\manyworlds\common\BasicCli;

class MwGenLst extends BasicCli {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableSCmd("generators",["usage" => "",
												  "help" => mc::_("List world generators"),
												  "permission" => "mw.cmd.world.create",
												  "aliases" => ["gen","genlst"]]);
	}
	public function onSCommand(CommandSender $c,Command $cc,$scmd,$data,array $args) {
		if (count($args) != 0) return false;

		if (MPMU::apiVersion("1.12.0")||MPMU::apiVersion("2.0.0")) {
			$c->sendMessage(implode(", ",Generator::getGeneratorList()));
		} else {
			$c->sendMessage("normal, flat");
			$c->sendMessage(TextFormat::RED.
								 mc::_("[MW] Plugin provided world generators\n are not included in\n this list."));
		}
		return true;
	}
}
