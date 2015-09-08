<?php
//= cmd:summon,Teleporting
//: Summons a player to your location
//> usage: **summon** _<player>_ _[message]_

//= cmd:dismiss,Teleporting
//: Dismiss a previously summoned player
//> usage: **dismiss** _<player>_ _[message]_
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;
use aliuly\grabbag\common\PermUtils;

class CmdSummon extends BasicCli implements CommandExecutor {

	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.summon", "summon|dismmiss command", "op");
		$this->enableCmd("summon",
							  ["description" => mc::_("Teleports players to your location"),
								"usage" => mc::_("/summon <player> [message]"),
								"permission" => "gb.cmd.summon"]);
		$this->enableCmd("dismiss",
							  ["description" => mc::_("Dismisses summoned players"),
								"usage" => mc::_("/dismiss <player|--all>"),
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

	public function cmdSummon(CommandSender $c,$args) {
		if (count($args) == 0) return false;
		if (!MPMU::inGame($c)) return true;
		$pl = $this->owner->getServer()->getPlayer($args[0]);
		if (!$pl) {
			$c->sendMessage(mc::_("%1% can not be found.",$args[0]));
			return true;
		}
		array_shift($args);
		if (count($args)) {
			$pl->sendMessage(implode(" ",$args));
		} else {
			$pl->sendMessage(mc::_("You have been summoned by %1%",$c->getName()));
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
		$c->sendMessage(mc::_("Summoning %1%....",$pn));

		$pl->teleport($c->getLevel()->getSafeSpawn($mv));
		return true;
	}
	public function cmdDismiss(CommandSender $c,$args) {
		if (count($args) == 0) return false;
		if (!MPMU::inGame($c)) return true;

		$state = $this->getState($c,[]);
		if (count($state) == 0) {
			$c->sendMessage(mc::_("There is nobody to dismiss"));
			$c->sendMessage(mc::_("You need to summon people first"));
			return true;
		}

		if ($args[0] == "--all") $args = array_keys($state);

		foreach ($args as $i) {
			$pl = $this->owner->getServer()->getPlayer($i);
			if (!$pl) {
				$c->sendMessage(mc::_("%1% can not be found.",$i));
				$i = strtolower($i);
				if (isset($state[$i])) unset($state[$i]);
				continue;
			}
			$pn = strtolower($pl->getName());
			if (!isset($state[$pn])) {
				$c->sendMessage(mc::_("%1% was never summoned",$i));
				continue;
			}
			$pl->sendMessage(mc::_("You have been dismissed by %1%",$c->getName()));
			$c->sendMessage(mc::_("Dismissing %1%",$i));
			$pl->teleport($state[$pn]);
			unset($state[$pn]);
		}
		$this->setState($c,$state);
		return true;
	}
}
