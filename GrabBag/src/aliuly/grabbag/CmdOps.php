<?php
/**
 ** OVERVIEW:Informational
 **
 ** COMMANDS
 **
 ** * ops: Shows who are the ops on this server.
 **   usage: **ops**
 **/
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\Player;
use pocketmine\utils\TextFormat;

class CmdOps extends BaseCommand {

	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("ops",
							  ["description" => "show ops and their on-line status",
								"usage" => "/ops",
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
				$txt[] = TextFormat::BLUE."$opname (online)";
			}else{
				$txt[] = TextFormat::RED."$opname";
			}
		}
		$txt[0] = "Server Ops (Online:$cnt)";
		return $this->paginateText($sender,$pageNumber,$txt);
	}
}
