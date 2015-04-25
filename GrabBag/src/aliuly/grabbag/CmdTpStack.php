<?php
/**
 ** OVERVIEW:Teleporting
 **
 ** COMMANDS
 **
 ** * pushtp : Saves current location and teleport
 **   usage: **pushtp** _<player>_ _[target]_
 ** * poptp : Returns to the previous location
 **   usage:: **poptp**
 **
 **/
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

class CmdTpStack extends BaseCommand {

	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("pushtp",
							  ["description" => "Save your current position when teleporting",
								"usage" => "/pushtp [player|position|world]",
								"permission" => "gb.cmd.pushpoptp"]);
		$this->enableCmd("poptp",
							  ["description" => "Returns to previously saved coordinates",
								"usage" => "/poptp",
								"permission" => "gb.cmd.pushpoptp"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		switch($cmd->getName()) {
			case "pushtp":
				return $this->cmdPushTp($sender,$args);
			case "poptp":
				return $this->cmdPopTp($sender,$args);
		}
		return false;
	}

	private function cmdPushTp(CommandSender $c,$args) {
		if (!$this->inGame($c)) return true;

		// Determine target...
		if (count($args) == 3 && is_numeric($args[0]) && is_numeric($args[1]) && is_numeric($args[2])) {
			$target = new Vector3($args[0],$args[1],$args[2]);
		} elseif (count($args) == 1 || count($args) == 4) {
			// is it a person or a world?...
			if (count($args) == 1
				 && ($pl = $this->owner->getServer()->getPlayer($args[0]))) {
				$target = $pl;
			} else {
				// Assume it is a level...
				$level = array_shift($args);
				if (count($args) == 3) {
					if (!(is_numeric($args[0]) && is_numeric($args[1]) && is_numeric($args[2]))) {
						$c->sendMessage("Invalid coordinate set");
						return true;
					}
					$cc = new Vector3($args[0],$args[1],$args[2]);
				} else {
					$cc = null;
				}
				if (!$this->owner->getServer()->isLevelLoaded($level)) {
					if (!$this->owner->getServer()->loadLevel($level)) {
						$c->sendMessage("Level not found $level");
						return true;
					}
				}
				$level = $this->owner->getServer()->getLevelByName($level);
				if (!$level) {
					$c->sendMesage("$level not found");
					return treu;
				}
				$target = $level->getSafeSpawn($cc);
			}
		} elseif (count($args) == 0) {
			$target = null;
		} else {
			return false;
		}

		// save location...
		$stack = $this->getState($c,[]);
		array_push($stack,new Position($c->getX(),$c->getY(),$c->getZ(),
												 $c->getLevel()));
		$this->setState($c,$stack);

		$c->sendMessage("Position saved!");
		if ($target) {
			$c->sendMessage("Teleporting...");
			$this->mwteleport($c,$target);
		}
		return true;
	}
	private function cmdPopTp(CommandSender $c,$args) {
		if (!$this->inGame($c)) return true;
		if (count($args)) return false;

		$stack = $this->getState($c,[]);
		if (count($stack) == 0) {
			$c->sendMessage("TpStack is empty");
			return true;
		}
		$pos = array_pop($stack);
		$c->sendMessage("Teleporting...");
		$this->mwteleport($c,$pos);
		$this->setState($c,$stack);
		return true;
	}
}
