<?php
//= cmd:blowup,Trolling
//: explode a player
//> usage: **blowup** _<player>_ _[yield]_ **[magic]** **[normal]**
//:
//: Explodes _player_ with an explosion with the given _yield_ (a number).
//: If **magic** is specified no damage will be taken by blocks.  The
//: default is **normal**, where blocks do get damaged.

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\PermUtils;

use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\level\Explosion;


class CmdBlowUp extends BasicCli implements CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.blowup", "Explode other players", "op");
		$this->enableCmd("blowup",
							  ["description" => mc::_("Explode a player"),
								"usage" => mc::_("/blowup <player> [yield|magic|normal]"),
								"permission" => "gb.cmd.blowup"]);
	}
	public function blowPlayer($pl,$yield,$magic) {
		$this->owner->getServer()->getPluginManager()->callEvent($cc = new ExplosionPrimeEvent($pl,$yield));
		if ($cc->isCancelled()) return false;
		$explosion = new Explosion($pl,$yield);
		if (!$magic) $explosion->explodeA();
		$explosion->explodeB();
		return true;
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "blowup") return false;
		if (count($args) == 0) return false;
		$pl = $this->owner->getServer()->getPlayer($args[0]);
		if (!$pl) {
			$sender->sendMessage(mc::_("%1% not found.",$args[0]));
			return true;
		}
		array_shift($args);
		$yield = 5;
		$magic = false;
		foreach ($args as $i) {
			if (is_numeric($i)) {
				$yield = intval($i);
				if ($yield < 1) $yield = 1;
			} elseif (strtolower($i) == "magic") {
				$magic = true;
			} elseif (strtolower($i) == "normal") {
				$magic = false;
			}
		}
		$this->blowPlayer($pl,$yield,$magic);
		return true;
	}
}
