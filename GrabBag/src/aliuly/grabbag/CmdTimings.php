<?php
//= cmd:showtimings,Informational
//: Shows timing repots as reported by **timings**
//> usage: **timings** _[t#]_
//:
//: If nothing specified it will list available reports.  These are
//: of the form of **timings.txt** or `timings1.txt`.
//:
//: To specify a report enter **t** for **timings.txt** or **t1** for
//: **timings1.txt**.
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\utils\TextFormat;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\PermUtils;

class CmdTimings extends BasicCli implements CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.timings", "view timings report", "op");
		$this->enableCmd("showtimings",
							  ["description" => mc::_("Show timings data (see /timings)"),
								"usage" => mc::_("/showtimings [t#]"),
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
				$sender->sendMessage(mc::_("Deleted reports: %1%",$count));
				return true;
			}
			$rpt = preg_replace('/[^0-9]+/i','',$rpt);
			$f = $this->owner->getServer()->getDataPath()."timings/timings$rpt.txt";
			if (!file_exists($f)) {
				$sender->sendMessage(mc::_("Report %1% can not be found.",$rpt));
				return true;
			}
			$txt = file($f);
			array_unshift($txt,mc::_("Report: timings%1%",$rpt));
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
			$sender->sendMessage(TextFormat::RED.mc::_("No timmings report found"));
			$sender->sendMessage(mc::_("Enable timings by typing /timings on"));
			$sender->sendMessage(mc::_("Generate timings report by typing /timings report"));
			return true;
		}
		$txt[0] = mc::_("Reports: %1%",$count);
		return $this->paginateText($sender,$pageNumber,$txt);
	}
}
