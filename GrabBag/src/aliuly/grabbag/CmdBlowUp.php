<?php
/**
 ** OVERVIEW:Trolling
 **
 ** COMMANDS
 **
 ** * blowup : explode a player
 **   usage: **blowup** _<player>_ _[yield]_ **[magic]** **[normal]**
 **
 **   Explodes `player` with an explosion with the given `yield` (a number).
 **   If `magic` is specified no damage will be taken by blocks.  The
 **   default is `normal`, where blocks do get damaged.
 **
 **/
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\level\Explosion;


class CmdBlowUp extends BaseCommand {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("blowup",
							  ["description" => "Explode a player",
								"usage" => "/blowup <player> [yield|magic|normal]",
								"permission" => "gb.cmd.blowup"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "blowup") return false;
		if (count($args) == 0) return false;
		$pl = $this->owner->getServer()->getPlayer($args[0]);
		if (!$pl) {
			$sender->sendMessage("$args[0] not found");
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
		$this->owner->getServer()->getPluginManager()->callEvent($cc = new ExplosionPrimeEvent($pl,$yield));
		if ($cc->isCancelled()) return true;
		$explosion = new Explosion($pl,$yield);
		if (!$magic) $explosion->explodeA();
		$explosion->explodeB();
		return true;
	}
}
