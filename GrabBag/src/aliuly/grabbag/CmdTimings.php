<?php
/**
 ** OVERVIEW:Informational
 **
 ** COMMANDS
 **
 ** * showtimings: Shows timing repots as reported by `/timings`
 **   usage: **timings** _[t#]_
 **
 **   If nothing specified it will list available reports.  These are
 **   of the form of `timings.txt` or `timings1.txt`.
 **
 **   To specify a report enter `t` for `timings.txt` or `t1` for
 **   `timings1.txt`.
 **/
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\utils\TextFormat;

class CmdTimings extends BaseCommand {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("showtimings",
							  ["description" => "Show timings data (see /timings)",
								"usage" => "/showtimings [t#]",
								"aliases" => ["who"],
								"permission" => "gb.cmd.timings"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "showtimings") return false;
		$pageNumber = $this->getPageNumber($args);
		if (count($args)) {
			// Show the specified report
			$rpt = array_shift($args);
			if ($rpt == "clear") {
				$count = 0;
				foreach (glob($this->owner->getServer()->getDataPath(). "timings/timings*.txt") as $f) {
					unlink($f); $count++;
				}
				$sender->sendMessage("Deleted reports: $count");
				return true;
			}
			$rpt = preg_replace('/[^0-9]+/i','',$rpt);
			$f = $this->owner->getServer()->getDataPath()."timings/timings$rpt.txt";
			if (!file_exists($f)) {
				$sender->sendMessage("Report $rpt can not be found");
				return true;
			}
			$txt = file($f);
			array_unshift($txt,"Report: timings$rpt");
			return $this->paginateText($sender,$pageNumber,$txt);
		}
		$txt = ["HDR"];
		// Inventorise the reports
		$count = 0;
		foreach (glob($this->owner->getServer()->getDataPath(). "timings/timings*.txt") as $f) {
			++$count;
			$txt[] = "- ".basename($f);
		}
		if ($count == 0) {
			$sender->sendMessage(TextFormat::RED."No timmings report found");
			$sender->sendMessage("Enable timings by typing /timings on");
			$sender->sendMessage("Generate timings report by typing /timings report");
			return true;
		}
		$txt[0] = "Reports: $count";
		return $this->paginateText($sender,$pageNumber,$txt);
	}
}
