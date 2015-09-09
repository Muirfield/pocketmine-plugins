<?php
//= cmd:burn,Trolling
//: Burns the specified player
//> usage: **burn** _<player>_ _[secs]_
//:
//: Sets _player_ on fire for the specified number of seconds.
//: Default is 15 seconds.
//:
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\PermUtils;


class CmdBurn extends BasicCli implements CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.burn", "Burn other players", "op");
		$this->enableCmd("burn",
							  ["description" => mc::_("Set player on fire"),
								"usage" => mc::_("/burn <player> [secs]"),
								"permission" => "gb.cmd.burn"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "burn") return false;
		if (count($args) > 2 || count($args) == 0) return false;
		$pl = $this->owner->getServer()->getPlayer($args[0]);
		if (!$pl) {
			$sender->sendMessage(mc::_("%1% not found",$args[0]));
			return true;
		}
		$secs = 15;
		if (isset($args[1])) $secs = intval($args[1]);
		if ($secs <= 1) $secs = 15;
		$pl->setOnFire($secs);
		return true;
	}
}
