<?php
/**
 ** OVERVIEW:Basic Usage
 **
 ** COMMANDS
 **
 ** * create : Creates a new world
 **   usage: /mw **create** _<world>_ _[seed]_ _[generator]_ _[preset]_
 **
 **  Creates a world named _world_.  You can optionally specify a _seed_
 **  as number, the generator (_flat_ or _normal_) and a _preset_ string.
 **
 **/
namespace aliuly\manyworlds;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\utils\TextFormat;
use pocketmine\level\generator\Generator;

use aliuly\manyworlds\common\mc;
use aliuly\manyworlds\common\BasicCli;

class MwCreate extends BasicCli {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableSCmd("create",["usage" => mc::_("<world> [seed] [generator] [preset]"),
										"help" => mc::_("Creates a new world"),
										"permission" => "mw.cmd.world.create",
										"aliases" => ["new"]]);
	}
	public function onSCommand(CommandSender $c,Command $cc,$scmd,$data,array $args) {
		if (count($args) < 1 || count($args)>4) return false;
		$world = array_shift($args);
		if ($this->owner->getServer()->isLevelGenerated($world)) {
			$c->sendMessage(TextFormat::RED.
								 mc::_("[MW] A world named %1% already exists",$world));
			return true;
		}
		$seed = null;
		$generator = null;
		$opts = [];
		if (isset($args[0])) $seed = intval($args[0]);
		if (isset($args[1])) {
			$generator = Generator::getGenerator($args[1]);
			if (strtolower($args[1]) != Generator::getGeneratorName($generator)){
				$c->sendMessage(TextFormat::RED.
									 mc::_("[MW] Unknown generator %1%",$args[1]));
				return true;
			}
			$c->sendMessage(TextFormat::GREEN.
								 mc::_("[MW] Using %1%",
										 Generator::getGeneratorName($generator)));
		}
		if(isset($args[2])) $opts = ["preset" => $args[2] ];
		$this->owner->getServer()->broadcastMessage(
			mc::_("[MW] Creating level %1%... (Expect Lag)", $world));
		$this->owner->getServer()->generateLevel($world,$seed,$generator,$opts);
		$this->owner->getServer()->loadLevel($world);
		return true;
	}
}
