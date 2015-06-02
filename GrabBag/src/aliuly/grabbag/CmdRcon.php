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

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;

class CmdRcon extends BasicCli implements CommandExecutor {
	protected $servers;

	public function __construct($owner,$cfg) {
		parent::__construct($owner);
		$this->servers = $cfg;
		$this->enableCmd("rcon",
							  ["description" => mc::_("RCON client"),
								"usage" => mc::_("/rcon [--add|--rm|--ls|id] <command>"),
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
		if (!MPMU::access($c,"gb.cmd.rcon.config")) return true;

		if (count($args) < 4) {
			$c->sendMessage(mc::_("Usage: --add <id> <host> <port> <auth> [comments]"));
			return false;
		}
		$id = array_shift($args);
		if (substr($id,0,1) == "-") {
			$c->sendMessage(mc::_("RCON id can not start with a dash (-)"));
			return false;
		}
		if (isset($this->servers[$id])) {
			$c->sendMessage(mc::_("%1% is an id that is already in use.",$id));
			$c->sendMessage(mc::_("Use --rm first"));
			return false;
		}
		$this->servers[$id] = implode(" ",$args);
		$this->owner->cfgSave("rcon-client",$this->servers);
		$c->sendMessage(mc::_("Rcon id %1% configured",$id));
		return true;
	}
	private function cmdRm(CommandSender $c,$args) {
		if (!MPMU::access($c,"gb.cmd.rcon.config")) return true;
		if (count($args) != 1) {
			$c->sendMessage(mc::_("Usage: --rm [id]"));
			return false;
		}
		$id = array_shift($args);
		if (!isset($this->servers[$id])) {
			$c->sendMessage(mc::_("%1% does not exist",$id));
			return false;
		}
		unset($this->servers[$id]);
		$this->owner->cfgSave("rcon-client",$this->servers);
		$c->sendMessage(mc::_("Rcon id %1% deleted",$id));
		return true;
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
			$c->sendMessage(mc::_("%1% does not exist",$id));
			return false;
		}
		$cmd = implode(" ",$args);
		$tt = new RconTask($c->getName(),$this->servers[$id],$cmd);
		$this->owner->getServer()->getScheduler()->scheduleAsyncTask($tt);
		return true;
	}
}
