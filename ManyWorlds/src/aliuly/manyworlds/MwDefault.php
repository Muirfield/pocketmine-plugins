<?php
/**
 ** OVERVIEW:Basic Usage
 **
 ** COMMANDS
 **
 ** * default : Sets the default world
 **   usage: /mw **default** _<world>_
 **
 **   Teleports you to another world.  If _player_ is specified, that
 **   player will be teleported.
 **/
namespace aliuly\manyworlds;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\utils\TextFormat;

use aliuly\manyworlds\common\mc;
use aliuly\manyworlds\common\BasicCli;

class MwDefault extends BasicCli {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableSCmd("default",["usage" => mc::_("<world>"),
											  "help" => mc::_("Changes default world"),
											  "permission" => "mw.cmd.default"]);
	}
	public function onSCommand(CommandSender $c,Command $cc,$scmd,$data,array $args) {
		if (count($args) == 0) return false;
		$wname =implode(" ",$args);
		$old = $this->owner->getServer()->getConfigString("level-name");
		if ($old == $wname) {
			$c->sendMessage(TextFormat::RED.mc::_("No change"));
			return true;
		}
		if (!$this->owner->autoLoad($c,$wname)) {
			$c->sendMessage(TextFormat::RED.
										mc::_("[MW] Unable to load %1%",$wname));
			$c->sendMessage(TextFormat::RED.mc::_("Change failed!"));
			return true;
		}
		$level = $this->owner->getServer()->getLevelByName($wname);
		if ($level === null) {
			$c->sendMessage(TextFormat::RED.mc::_("Error GetLevelByName %1%"));
			return true;
		}
		$this->owner->getServer()->setConfigString("level-name",$wname);
		$this->owner->getServer()->setDefaultLevel($level);
		$c->sendMessage(TextFormat::BLUE.mc::_("Default world changed to %1%",$wname));
		return true;
	}
}
