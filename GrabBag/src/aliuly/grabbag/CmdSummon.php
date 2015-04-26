<?php
/**
 ** OVERVIEW:Teleporting
 **
 ** COMMANDS
 **
 ** * summon : Summons a player to your location
 **   usage: **summon** _<player>_ _[message]_
 ** * dismiss : Dismiss a previously summoned player
 **   usage: **dismiss** _<player>_ _[message]_
 **
 **/
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

class CmdSummon extends BaseCommand {

	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("summon",
							  ["description" => "Teleports players to your location",
								"usage" => "/summon <player> [message]",
								"permission" => "gb.cmd.summon"]);
		$this->enableCmd("dismiss",
							  ["description" => "Dismisses summoned players",
								"usage" => "/dismiss <player|--all>",
								"permission" => "gb.cmd.summon"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		switch($cmd->getName()) {
			case "summon":
				return $this->cmdSummon($sender,$args);
			case "dismiss":
				return $this->cmdDismiss($sender,$args);
		}
		return false;
	}

	private function cmdSummon(CommandSender $c,$args) {
		if (count($args) == 0) return false;
		if (!$this->inGame($c)) return true;
		$pl = $this->owner->getServer()->getPlayer($args[0]);
		if (!$pl) {
			$c->sendMessage("$args[0] can not be found");
			return true;
		}
		array_shift($args);
		if (count($args)) {
			$pl->sendMessage(implode(" ",$args));
		} else {
			$pl->sendMessage("You have been summoned by ".$c->getName());
		}

		// Do we need to save current location?
		$state = $this->getState($c,[]);
		$pn = strtolower($pl->getName());
		if (!isset($state[$pn])) {
			$state[$pn] = new Position($pl->getX(),$pl->getY(),$pl->getZ(),
												$pl->getLevel());
		}
		$this->setState($c,$state);
		$mv = new Vector3($c->getX()+mt_rand(-3,3),$c->getY(),
								$c->getZ()+mt_rand(-3,3));
		$c->sendMessage("Summoning $pn....");

		$this->mwteleport($pl,$c->getLevel()->getSafeSpawn($mv));
		return true;
	}
	private function cmdDismiss(CommandSender $c,$args) {
		if (count($args) == 0) return false;
		if (!$this->inGame($c)) return true;

		$state = $this->getState($c,[]);
		if (count($state) == 0) {
			$c->sendMessage("There is nobody to dismiss");
			$c->sendMessage("You need to summon people first");
			return true;
		}

		if ($args[0] == "--all") $args = array_keys($state);

		foreach ($args as $i) {
			$pl = $this->owner->getServer()->getPlayer($i);
			if (!$pl) {
				$c->sendMessage("$i can not be found");
				$i = strtolower($i);
				if (isset($state[$i])) unset($state[$i]);
				continue;
			}
			$pn = strtolower($pl->getName());
			if (!isset($state[$pn])) {
				$c->sendMessage("$i was never summoned");
				continue;
			}
			$pl->sendMessage("You have been dismissed by ".$c->getName());
			$c->sendMessage("Dismissing $i");
			$this->mwteleport($pl,$state[$pn]);
			unset($state[$pn]);
		}
		$this->setState($c,$state);
		return true;
	}
}
