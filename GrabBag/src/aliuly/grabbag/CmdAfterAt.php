<?php
//= cmd:after,Server_Management
//: schedule command after a number of seconds
//> usage: **after** _<seconds>_ _<command>|list|cancel_ _<id>_
//:
//: Will schedule to run *command* after *seconds*.
//: The **list** sub command will show all the queued commands.
//: The **cancel** sub command allows you to cancel queued commands.
//:
//= cmd:at,Server_Management
//: schedule command at an appointed date/time
//> usage: **at** _<time>_ _[:]_ _<command>|list|cancel _<id>_
//:
//: Will schedule to run *command* at the given date/time.  This uses
//: php's [strtotime](http://php.net/manual/en/function.strtotime.php)
//: function so _times_ must follow the format described in
//: [Date and Time Formats](http://php.net/manual/en/datetime.formats.php).
//: The **list** sub command will show all the queued commands.
//: The **cancel** sub command allows you to cancel queued commands.
//:
//= cmdnotes
//:
//: Commands scheduled by **at** and **after** will only run as
//: long as the server is running.  These scheduled commands will *not*
//: survive server reloads or reboots.  If you want persistent commands,
//: it is recommended that you use a plugin like
//: [TimeCommander](http://forums.pocketmine.net/plugins/timecommander.768/).
//:


namespace aliuly\grabbag;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\PluginCallbackTask;
use aliuly\grabbag\common\PermUtils;

class CmdAfterAt extends BasicCli implements CommandExecutor {
	protected $tasks;
	public function __construct($owner) {
		parent::__construct($owner);
		$this->tasks = [];

		PermUtils::add($this->owner, "gb.cmd.after", "access command scheduler", "op");

		$this->enableCmd("after",
							  ["description" => mc::_("schedule to run a command after x seconds"),
								"usage" => mc::_("/after <seconds> <command>|list|cancel <id>"),
								"permission" => "gb.cmd.after"]);
		$this->enableCmd("at",
							  ["description" => mc::_("schedule to run a command at a certain time"),
								"usage" => mc::_("/at <time> <command>|list|cancel <id>"),
								"permission" => "gb.cmd.after"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		// Collect expired tasks out of the tasks table...
		foreach (array_keys($this->tasks) as $tid) {
			if (!$this->owner->getServer()->getScheduler()->isQueued($tid)) {
				unset($this->tasks[$tid]);
			}
		}
		switch($cmd->getName()) {
			case "after":
			  if ($this->commonSubs($sender,$args)) return true;
				return $this->cmdAfter($sender,$args);
			case "at":
				if ($this->commonSubs($sender,$args)) return true;
				return $this->cmdAt($sender,$args);
		}
		return false;
	}
	public function runCommand($cmd) {
		$this->owner->getServer()->dispatchCommand(new ConsoleCommandSender(),$cmd);
	}
	private function commonSubs(CommandSender $c,$args){
		if (count($args) == 0) return false;
		switch (strtolower($args[0])){
			case "list":
			case "ls":
				if (count($this->tasks) == 0) {
					$c->sendMessage(mc::_("No tasks currently scheduled"));
					return true;
				}
				$pageNumber = $this->getPageNumber($args);
				$tab = [ [	mc::_("Id"), mc::_("When"),
										mc::n(mc::_("One scheduled task"),
											 mc::_("%1% scheduled tasks",count($this->tasks)),
											count($this->tasks)) ] ];
				foreach ($this->tasks as $tid => $cmd) {
					list($when,$line) = $cmd;
					$tab[] = [ $tid, date(mc::_("d-M-Y H:i:s"),$when), $line ];
				}
				return $this->paginateTable($c,$pageNumber,$tab);
			case "cancel":
				if (count($args) != 2) return false;
				if (!isset($this->tasks[$args[1]])){
					$c->sendMessage(mc::_("Task %1% not found!",$args[1]));
					return true;
				}
				$this->owner->getServer()->getScheduler()->cancelTask($args[1]);
				$c->sendMessage(mc::_("Cancelling Task %1%",$args[1]));
				return true;
		}
		return false;
	}
  public function schedule($secs,$cmdline) {
		$h = $this->owner->getServer()->getScheduler()->scheduleDelayedTask(
			new PluginCallbackTask($this->owner,[$this,"runCommand"],[$cmdline]),
			$secs * 20
		);
		$this->tasks[$h->getTaskId()] = [time()+$secs,$cmdline];
	}
	private function cmdAfter(CommandSender $c,$args) {
		if (count($args) < 2) return false;
		if (!is_numeric($args[0])) {
			$c->sendMessage(mc::_("Unable to specify delay %1%",$args[0]));
			return false;
		}
		$secs = array_shift($args);
		$c->sendMessage(mc::_("Scheduled for %1%",date(DATE_RFC2822,time()+$secs)));
		$h = $this->owner->getServer()->getScheduler()->scheduleDelayedTask(
			new PluginCallbackTask($this->owner,[$this,"runCommand"],[implode(" ",$args)]),
			$secs * 20
		);
		$this->tasks[$h->getTaskId()] = [time()+$secs,implode(" ",$args)];
		return true;
	}
	private function cmdAt(CommandSender $c,$args) {
		if (count($args) < 2) {
			$c->sendMessage(mc::_("Time now is: %1%",date(DATE_RFC2822)));
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
			$c->sendMessage(mc::_("Unable to parse time specification %1%",$ts));
			return false;
		}
		while ($when < time()) {
			$when += 86400; // We can not travel back in time...
		}
		$c->sendMessage(mc::_("Scheduled for %1%",date(DATE_RFC2822,$when)));
		$h = $this->owner->getServer()->getScheduler()->scheduleDelayedTask(
			new PluginCallbackTask($this->owner,[$this,"runCommand"],[implode(" ",$args)]),
			($when - time())*20
		);
		$this->tasks[$h->getTaskId()] = [$when, implode(" ",$args)];
		return true;
	}
}
