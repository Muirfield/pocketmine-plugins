<?php
/**
 ** OVERVIEW:Server Management
 **
 ** COMMANDS
 **
 ** * crash : manage crash dumps
 **   usage: **crash** _[ls|clean|show]_
 **
 **   Will show the number of `crash` files in the server.
 **   The following optional sub-commands are available:
 **   - **crash** **count**
 **     - Count the number of crash files
 **   - **crash** **ls** _[patthern]_
 **     - List crash files
 **   - **crash** **clean** _[pattern]_
 **     - Delete crash files
 **   - **show** _[pattern]_
 **     - Shows the crash file ##
 **
 **/

namespace aliuly\grabbag;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;

class CmdCrash extends BasicCli implements CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("crash",
							  ["description" => mc::_("manage crash files"),
								"usage" => mc::_("/crash [count|clean|show|ls]"),
								"permission" => "gb.cmd.crash"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "crash") return false;
		if (count($args) == 0) $args = [ "count" ];
		$scmd = strtolower(array_shift($args));

		switch($scmd) {
			case "count":
				return $this->cmdCount($sender);
			case "clean":
				return $this->cmdClean($sender,$args);
			case "show":
				return $this->cmdShow($sender,$args);
			case "ls":
				return $this->cmdLs($sender,$args);
				break;
			default:
				$sender->sendMessage(mc::_("Unknown sub-command %1%",$scmd));
		}
		return false;
	}
	private function getCrashDumps($pattern = "*") {
		return glob($this->owner->getServer()->getDataPath()."CrashDump_$pattern.log");
	}
	private function cmdCount(CommandSender $c) {
		$cnt = count($this->getCrashDumps());
		$c->sendMessage(mc::_("Total Crash Dumps: %1%",$cnt));
		return true;
	}
	private function cmdClean(CommandSender $c,$args) {
		if (count($args) == 0) $args[] = "*";
		if (count($args) != 1) return false;
		$cnt = 0;
		foreach ($this->getCrashDumps($args[0]) as $cd) {
			if (is_file($cd)) {
				unlink($cd);
				++$cnt;
			}
		}
		$c->sendMessage(mc::_("Crash Dumps Deleted: %1%",$cnt));
		return true;
	}
	private function cmdLs(CommandSender $c,$args) {
		$pageNumber = $this->getPageNumber($args);
		if (count($args) == 0) $args[] = "*";
		if (count($args) != 1) return false;
		$dumps = $this->getCrashDumps($args[0]);
		if (count($dumps) == 0) {
			$c->sendMessage(mc::_("No crash dumps found"));
			return true;
		}
		$i = 1;
		$txt = [ mc::_("Crash Dumps: %1%",count($dumps)) ];
		foreach ($dumps as $dump) {
			$txt[] = mc::_("%1%) %2%", $i++,
								preg_replace('/^CrashDump_/','',basename($dump)));
		}
		return $this->paginateText($c,$pageNumber,$txt);
	}
	private function cmdShow(CommandSender $c,$args) {
		$pageNumber = $this->getPageNumber($args);
		if (count($args) == 0) $args[] = "*";
		if (count($args) != 1) return false;
		$dumps = $this->getCrashDumps($args[0]);
		if (count($dumps) == 0) {
			$c->sendMessage(mc::_("No crash dumps found"));
			return true;
		}
		$f = array_shift($dumps);
		$txt = file($f,FILE_IGNORE_NEW_LINES);
		array_unshift($txt,mc::_("Crash Dump %1%",
										 preg_replace('/^CrashDump_/','',basename($f))));
		if (count($dumps) > 0) {
			array_unshift($txt,TextFormat::RED.
							  mc::_("Multiple matches, showing first match!"));
		}
		print_r($txt);
		return $this->paginateText($c,$pageNumber,$txt);
	}
}
