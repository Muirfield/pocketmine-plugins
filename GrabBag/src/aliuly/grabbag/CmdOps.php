<?php
//= cmd:ops,Informational
//: Shows who are the ops on this server.
//> usage: **ops**

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\Player;
use pocketmine\utils\TextFormat;

use aliuly\grabbag\common\PermUtils;
use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;

class CmdOps extends BasicCli implements CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.ops", "Display ops", "true");
		$this->enableCmd("ops",
							  ["description" => mc::_("show ops and their on-line status"),
								"usage" => mc::_("/ops"),
								"permission" => "gb.cmd.ops"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "ops") return false;

		$txt = [ "" ];
		$pageNumber = $this->getPageNumber($args);
		$cnt=0;
		foreach (array_keys($this->owner->getServer()->getOps()->getAll()) as $opname) {
			$p = $this->owner->getServer()->getPlayer($opname);
			if($p && ($p->isOnline() && (!($sender instanceof Player) || $sender->canSee($p)))){
				++$cnt;
				$txt[] = TextFormat::BLUE.mc::_("%1% (online)",$opname);
			}else{
				$txt[] = TextFormat::RED."$opname";
			}
		}
		$txt[0] = mc::_("Server Ops (Online:%1%)",$cnt);
		return $this->paginateText($sender,$pageNumber,$txt);
	}
}
