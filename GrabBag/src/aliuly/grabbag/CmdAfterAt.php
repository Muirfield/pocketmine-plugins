<?php
namespace aliuly\grabbag;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\scheduler\CallbackTask;

class CmdAfterAt extends BaseCommand {

	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("after",
							  ["description" => "schedule to run a command after x seconds",
								"usage" => "/after <seconds> <command>",
								"permission" => "gb.cmd.after"]);
		$this->enableCmd("at",
							  ["description" => "schedule to run a command at a certain time",
								"usage" => "/at <time> <command>",
								"permission" => "gb.cmd.after"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		switch($cmd->getName()) {
			case "after":
				return $this->cmdAfter($sender,$args);
			case "at":
				return $this->cmdAt($sender,$args);
		}
		return false;
	}
	public function runCommand($cmd) {
		$this->owner->getServer()->dispatchCommand(new ConsoleCommandSender(),$cmd);
	}

	private function cmdAfter(CommandSender $c,$args) {
		if (count($args) < 2) return false;
		if (!is_numeric($args[0])) {
			$c->sendMessage("Unable to specify delay $args[0]");
			return false;
		}
		$secs = intval(array_shift($args));
		$c->sendMessage("Scheduled for ".date(DATE_RFC2822,time()+$secs));
		$this->owner->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this,"runCommand"],[implode(" ",$args)]),$secs * 20);
		return true;
	}
	private function cmdAt(CommandSender $c,$args) {
		if (count($args) < 2) {
			$c->sendMessage("Time now is: ".date(DATE_RFC2822));
			return false;
		}
		if (($pos = array_search(":",$args)) != false) {
			if ($pos == 0) return false;
			$ts = [];
			while ($pos--) {
				$ts[] = array_shift($args);
			}
			array_shift($args);
			if (count($args) == 0) return false;
			$ts = implode(" ",$ts);
			$when = strtotime($ts);
		} else {
			for ($ts = array_shift($args);
				  ($when = strtotime($ts)) == false && count($args) > 1;
				  $ts .= ' '.array_shift($args)) ;
		}
		if ($when == false) {
			$c->sendMessage("Unable to parse time specification $ts");
			return false;
		}
		while ($when < time()) {
			$when += 86400; // We can not travel back in time...
		}
		$c->sendMessage("Scheduled for ".date(DATE_RFC2822,$when));
		$this->owner->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this,"runCommand"],[implode(" ",$args)]),($when - time())*20);
		return true;
	}
}
