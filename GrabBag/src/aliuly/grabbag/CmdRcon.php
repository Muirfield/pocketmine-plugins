<?php
//= cmd:rcon,Server_Management
//: rcon client
//> usage: **rcon** _<id>_ _<command>_
//:
//: This is an rcon client that you can used to send commands to other
//: remote servers identified by **id**.
//:
//: You can specify multiple targets by separating with commas (,).
//: Otherwise, you can use **--all** keyword for the _id_ if you want to
//: send the commands to all configured servers.
//:
//: Use the **servers** command to define the rcon servers.

namespace aliuly\grabbag;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\RemoteConsoleCommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;
use aliuly\grabbag\common\RconTask;
use aliuly\grabbag\common\Rcon;
use aliuly\grabbag\common\PermUtils;

class CmdRcon extends BasicCli implements CommandExecutor {
	protected $servers;

	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.rcon", "Rcon client", "op");

		$this->enableCmd("rcon",
							  ["description" => mc::_("RCON client"),
								"usage" => mc::_("/rcon <id> <command>"),
								"permission" => "gb.cmd.rcon"]);
	}
	public function onCommand(CommandSender $c,Command $cmd,$label, array $args) {
		if (count($args) == 0) return false;
		if ($cmd->getName() != "rcon") return false;

		if (count($args) < 2) return false;
		$id = array_shift($args);
		if ($id == "--all") {
			$grp = $this->owner->getModule("ServerList")->getIds();
		} else {
			$grp = [];
			foreach (explode(",",$id) as $i) {
				if ($this->owner->getModule("ServerList")->getServer($i) === null) {
			  	$c->sendMessage(mc::_("%1% does not exist",$id));
			  	continue;
		  	}
				$grp[$i] = $i;
			}
			if (count($grp) == 0) return false;
		}
		$cmd = implode(" ",$args);
		if ($c instanceof RemoteConsoleCommandSender) {
			// This is an Rcon connection itself... we run in the foreground
			foreach ($grp as $id) {
				$dat = $this->owner->getModule("ServerList")->getServer($id);
				if (!isset($dat["rcon-pw"])) {
					$c->sendMessage(mc::_("Peer %1% does not have an rcon password defined", $id));
					continue;
				}
				$host = $dat["host"];
				$port = isset($dat["rcon-port"]) ? $dat["rcon-port"] : $dat["port"];
				$auth = $dat["rcon-pw"];

				$ret = Rcon::connect($host,$port,$auth);
				if (!is_array($ret)) {
					$c->sendMessage($ret);
					continue;
				}
				list($sock,$id) = $ret;
				$ret = Rcon::cmd($cmd,$sock,$id);
				if (is_array($ret)) {
					$c->sendMessage($ret[0]);
				} else {
					$c->sendMessage($ret);
				}
				fclose($sock);
			}
			return true;
		}
		foreach ($grp as $id) {
			$dat = $this->owner->getModule("ServerList")->getServer($id);
			if (!isset($dat["rcon-pw"])) {
				$c->sendMessage(mc::_("Peer %1% does not have an rcon password defined", $id));
				continue;
			}
			$host = $dat["host"];
			$port = isset($dat["rcon-port"]) ? $dat["rcon-port"] : $dat["port"];
			$auth = $dat["rcon-pw"];

			$this->owner->getServer()->getScheduler()->scheduleAsyncTask(
				new RconTask($this->owner,"rconDone",
											[$host,$port,$auth],
											$cmd, [($c instanceof Player) ? $c->getName() : null])

		  );
		}
		return true;
	}
	public function taskDone($res,$sn) {
		if ($sn === null) {
			$player = new ConsoleCommandSender();
		} elseif (($player = $this->owner->getServer()->getPlayer($sn)) == null) {
			return; // Output discarded!
		}
		if (!is_array($res)) {
			$player->sendMessage($res);
			return;
		}
		$player->sendMessage($res[0]);
	}
}
