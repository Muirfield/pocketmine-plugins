<?php
//= cmd:reg,Player_Management
//: Manage player registrations
//> usage: **reg** _[subcommand]_ _[options]_
//:
//: By default it will show the number of registered players.  The following
//: sub-commands are available:
//> - **count**
//:   - default sub-command.  Counts the number of registered players.
//> - **list** _[pattern]_
//:   - Display a list of registered players or those that match the
//:     wildcard _pattern_.
//> - **rm** _<player>_
//:   - Removes _player_ registration.
//> - **since** _<when>_
//:   - Display list of players registered since a date/time.


namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\Player;
use pocketmine\utils\TextFormat;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;
use aliuly\grabbag\common\PermUtils;


class CmdRegMgr extends BasicCli implements CommandExecutor {

	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.regs", "Manage player registrations", "op");
		$this->enableCmd("reg",
							  ["description" => mc::_("manage player registrations"),
								"usage" => mc::_("/reg [count|list [pattern]|rm [player]|since <when>]"),
								"permission" => "gb.cmd.regs"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "reg") return false;
		if (count($args) == 0) $args = [ "count" ];
		$scmd = strtolower(array_shift($args));
		switch ($scmd) {
			case "count":
				if (count($args) != 0) return false;
				$cnt = count(glob($this->owner->getServer()->getDataPath()."players/*.dat"));
				$sender->sendMessage(mc::n(mc::_("One player registered"),
													mc::_("%1% players registered",$cnt),
													$cnt));
				return true;
			case "ls":
			case "list":
				$pageNumber = $this->getPageNumber($args);
				if (count($args) == 0) {
					$pattern = "*";
				} elseif (count($args) == 1) {
					$pattern = implode(" ",$args);
				} else {
					return false;
				}
				$f = glob($this->owner->getServer()->getDataPath()."players/".
							 $pattern.".dat");
				$txt = [ mc::n(mc::_("One player found"),
									mc::_("%1% players found",count($f)),count($f)) ];
				$cols = 8;
				$i = 0;
				foreach ($f as $n) {
					$n = basename($n,".dat");
					if (($i++ % $cols) == 0) {
						$txt[] = $n;
					} else {
						$txt[count($txt)-1] .= ", ".$n;
					}
				}
				return $this->paginateText($sender,$pageNumber,$txt);
			case "rm":
				if (count($args) != 1) return false;
				$victim = strtolower(array_shift($args));
				$target = $this->owner->getServer()->getPlayer($victim);
				if ($target !== null) {
					$sender->sendMessage(TextFormat::RED.
												mc::_("Can not delete player re-gistration while they are on-line"));
					return true;
				}
				$target = $this->owner->getServer()->getOfflinePlayer($victim);
				if ($target == null || !$target->hasPlayedBefore()) {
					$sender->sendMessage(mc::_("%1% can not be found.",$victim));
					return true;
				}
				$f = $this->owner->getServer()->getDataPath()."players/".$victim.".dat";
				if (!is_file($f)) {
					$sender->sendMessage(TextFormat::RED.mc::_("Problem deleting %1%",$victim));
					return true;
				}
				unlink($f);
				$sender->sendMessage(TextFormat::RED.mc::_("%1% was deleted.",$victim));
				return true;
			case "since":
				$pageNumber = $this->getPageNumber($args);
				if (count($args) == 0) return false;
				if (($when = strtotime(implode(" ",$args))) === false) return false;
				$f = glob($this->owner->getServer()->getDataPath()."players/*.dat");
				$tab = [ [ mc::_("Date/Time"), "x" ] ];
				foreach ($f as $n) {
					$n = basename($n,".dat");
					$target = $this->owner->getServer()->getOfflinePlayer($n);
					if ($target == null || !$target->hasPlayedBefore()) continue;
					if (($regdate = $target->getFirstPlayed()/1000) > $when) {
						$tab[] = [ date(mc::_("d-M-Y H:i"),$regdate), $n ];
					}
				}
				$cnt = count($tab)-1;
				if ($cnt == 0) {
					$sender->sendMessage(mc::_("No players found"));
					return true;
				}
				$tab[0][1] = mc::n(mc::_("One player found"),
														mc::_("%1% players found",$cnt),
														$cnt);
				return $this->paginateTable($sender,$pageNumber,$tab);

		}
		return false;
	}
}
