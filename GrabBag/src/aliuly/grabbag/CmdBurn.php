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
use aliuly\common\BasicCli;
use aliuly\common\mc;
use aliuly\common\PermUtils;
use aliuly\common\MPMU;

class CmdBurn extends BasicCli implements CommandExecutor {
	const DEFAULTSECS = 5;
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
		if (($pl = MPMU::getPlayer($sender,$args[0])) === null) return true;
		$secs = self::DEFAULTSECS;
		if (isset($args[1])) $secs = intval($args[1]);
		if ($secs <= 1) $secs = self::DEFAULTSECS;
		$pl->setOnFire($secs);
		return true;
	}
}
