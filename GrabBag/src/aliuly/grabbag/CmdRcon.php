<?php
/**
 ** OVERVIEW:Server Management
 **
 ** COMMANDS
 **
 ** * rcon : rcon client
 **   usage: **rcon** **[--add|--rm|--ls|id]** _<command>_
 **
 **   This is an rcon client that you can used to send commands to other
 **   remote servers.  Options:
 **   - **rcon --add** _<id>_ _<address>_ _<port>_ _<password>_ _[comments]_
 **     - adds a `rcon` connection with `id`.
 **   - **rcon --rm** _<id>_
 **     - Removes `rcon` connection `id`.
 **   - **rcon --ls**
 **     - List configured rcon connections.
 **   - **rcon** _<id>_ _<command>_
 **     - Sends the `command` to the connection `id`.
 **   should use the `rpt` command.
 **
 ** * rpt : report an issue to ops
 **   usage: **rpt** [_message_|**read|clear** _<all|##>_]
 **
 **   Logs/reports an issue to server ops.  These issues are stored in a
 **   a file which can be later read by the server operators.  Use this
 **   when there are **no** ops on-line.  If there are ops on-line you
 **   should use the `opms` command.
 **
 ** CONFIG:rcon-client
 **
 ** This section configures the rcon client connections.  You can configure
 ** this section through the *rcon* command.
 **/
namespace aliuly\grabbag;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;


class CmdRcon extends BaseCommand {
	protected $servers;

	public function __construct($owner,$cfg) {
		parent::__construct($owner);
		$this->servers = $cfg;
		$this->enableCmd("rcon",
							  ["description" => "RCON client",
								"usage" => "/rcon [--add|--rm|--ls|id] <command>",
								"permission" => "gb.cmd.rcon"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if (count($args) == 0) return false;
		switch($cmd->getName()) {
			case "rcon":
				switch (strtolower($args[0])) {
					case "--add":
						array_shift($args);
						return $this->cmdAdd($sender,$args);
					case "--rm":
						array_shift($args);
						return $this->cmdRm($sender,$args);
					case "--ls":
						array_shift($args);
						return $this->cmdList($sender,$args);
					default:
						return $this->cmdRcon($sender,$args);
				}
		}
		return false;
	}
	private function cmdAdd(CommandSender $c,$args) {
		if (!$this->access($c,"gb.cmd.rcon.config")) return true;

		if (count($args) < 4) {
			$c->sendMessage("Usage: --add <id> <host> <port> <auth> [comments]");
			return false;
		}
		$id = array_shift($args);
		if (substr($id,0,1) == "-") {
			$c->sendMessage("RCON id can not start with a dash (-)");
			return false;
		}
		if (isset($this->servers[$id])) {
			$c->sendMessage("$id is an id that is already in use.");
			$c->sendMessage("Use --rm first");
			return false;
		}
		$this->servers[$id] = implode(" ",$args);
		$this->cfgSave("rcon-client",$this->servers);
		$c->sendMessage("Rcon id $id configured");
		return true;
	}
	private function cmdRm(CommandSender $c,$args) {
		if (!$this->access($c,"gb.cmd.rcon.config")) return true;
		if (count($args) != 1) {
			$c->sendMessage("Usage: --rm [id]");
			return false;
		}
		$id = array_shift($args);
		if (!isset($this->servers[$id])) {
			$c->sendMessage("$id does not exist");
			return false;
		}
		unset($this->servers[$id]);
		$this->cfgSave();
		$c->sendMessage("Rcon id $id deleted");
	}
	private function cmdList(CommandSender $c,$args) {
		foreach ($this->servers as $id => $dat) {
			$dat = preg_split('/\s+/',$dat,4);
			$host = array_shift($dat);
			$port = array_shift($dat);
			array_shift($dat);
			$txt = count($dat) ? " #".$dat[0] : "";
			$c->sendMessage("$id: $host:$port$txt");
		}
		return true;
	}
	private function cmdRcon(CommandSender $c,$args) {
		if (count($args) < 2) return false;
		$id = array_shift($args);
		if (!isset($this->servers[$id])) {
			$c->sendMessage("$id does not exist");
			return false;
		}
		$cmd = implode(" ",$args);
		$tt = new RconTask($c->getName(),$this->servers[$id],$cmd);
		$this->owner->getServer()->getScheduler()->scheduleAsyncTask($tt);
		return true;
	}
}
