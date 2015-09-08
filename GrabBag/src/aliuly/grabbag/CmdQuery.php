<?php
//= cmd:query,Server_Management
//: query remote servers
//> usage: **query** **[list|info|plugins|players|summary]** _[opts]_
//:
//: This is a query client that you can use to query other
//: remote servers.
//:
//: Servers are defined with the **servers** command.
//:
//: Options:
//> - **query list**
//:     - List players on all configured `query` connections.
//> - **query info** _<id>_
//:     - Return details from query
//> - **query players** _<id>_
//:     - Return players on specified server
//> - **query plugins** _<id>_
//:     - Returns plugins on specified server
//> - **query summary**
//:     - Summary of server data

namespace aliuly\grabbag;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;
use aliuly\grabbag\common\PermUtils;

use xPaw\MinecraftQuery;
use xPaw\MinecraftQueryException;

class CmdQuery extends BasicCli implements CommandExecutor {

	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.query", "Query command", "true");
		PermUtils::add($this->owner, "gb.cmd.query.details", "View details (info, plugins)", "true");
		PermUtils::add($this->owner, "gb.cmd.query.players", "View players", "true");
		PermUtils::add($this->owner, "gb.cmd.query.players.showip", "View players server IP", "true");
		PermUtils::add($this->owner, "gb.cmd.query.list", "Query List sub command", "true");

		$this->enableCmd("query",
							  ["description" => mc::_("Query servers"),
								"usage" => mc::_("/query [list|info|plugins|players|summary] <opts>"),
								"permission" => "gb.cmd.query"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if (count($args) == 0) $args = ["summary"];
		switch($cmd->getName()) {
			case "query":
				switch ($n = strtolower(array_shift($args))) {
					case "info":
					case "plugins":
						if (!MPMU::access($sender,"gb.cmd.query.details")) return true;
						return $this->cmdQuery($sender,$n,$args);
					case "players":
						if (!MPMU::access($sender,"gb.cmd.query.players")) return true;
						$pageNumber = $this->getPageNumber($args);
					  if (count($args) && $sender->hasPermission("gb.cmd.query.players.showip")) {
							return $this->cmdQuery($sender,$n,$args,$pageNumber);
						}
						return $this->cmdPlayers($sender,$pageNumber);
					case "list":
							if (!MPMU::access($sender,"gb.cmd.query.list")) return true;
							$pageNumber = $this->getPageNumber($args);
							return $this->cmdListAll($sender,$pageNumber);
					case "summary":
					  return $this->cmdSummary($sender);
				}
		}
		return false;
	}
	private function cmdQuery(CommandSender $c,$q,$args,$pageNumber = -1) {
		if ($pageNumber == -1) $pageNumber = $this->getPageNumber($args);
		if (count($args) != 1) {
			$c->sendMessage(TextFormat::RED.mc::_("Usage: %1% <id>",$q));
			return false;
		}
		$id = array_shift($args);
		if (($dat = $this->owner->getModule("ServerList")->getServer($id)) === null) {
			$c->sendMessage(TextFormat::RED.mc::_("%1% does not exist",$id));
			return false;
		}
		$host = $dat["host"];
		$port = $dat["port"];

		$Query = new MinecraftQuery( );
		try {
			$Query->Connect( $host, $port, 1 );
		} catch (MinecraftQueryException $e) {
			$c->sendMessage(TextFormat::RED.mc::_("Query %1% failed: %2%",$host,$e->getMessage()));
			return true;
		}
		$txt = [ mc::_("[%3%] query for %1%:%2%", $host,$port,$q) ];
		switch ($q) {
			case "info":
			  if (($info = $Query->GetInfo()) === false) {
					$c->sendMessage(TextFormat::RED.mc::_("Query of %1%:%2% returned no data", $host,$port));
					return true;
				}
				foreach ($info as $i=>$j) {
					if ($i == "RawPlugins") continue;
					if (is_array($j)) continue;
					$txt[] =  TextFormat::GREEN. $i.": ".TextFormat::WHITE.$j;
				}
				break;
			case "plugins":
				if (($info = $Query->GetInfo()) === false) {
					$c->sendMessage(TextFormat::RED.mc::_("Query of %1%:%2% returned no data", $host,$port));
					return true;
				}
				if (!isset($info["Plugins"])) {
					$c->sendMessage(TextFormat::RED.mc::_("%1%:%2%: No plugins", $host,$port));
					return true;
				}
				$cols = 8;
				$i = 0;
				foreach ($info["Plugins"] as $n) {
					if (($i++ % $cols) == 0) {
						$txt[] = $n;
					} else {
						$txt[count($txt)-1] .= ", ".$n;
					}
				}
				break;
			case "players":
				if (($players = $Query->GetPlayers()) === false) {
					$c->sendMessage(TextFormat::RED.mc::_("Query of %1%:%2% returned no data", $host,$port));
					return true;
				}
				if (count($players) == 0) {
					$c->sendMessage(TextFormat::RED.mc::_("%1%:%2%: No players", $host,$port));
					return true;
				}
				$cols = 8;
				$i = 0;
				foreach ($players as $n) {
					if (($i++ % $cols) == 0) {
						$txt[] = $n;
					} else {
						$txt[count($txt)-1] .= ", ".$n;
					}
				}
				break;
			default:
			  return false;
		}
		return $this->paginateText($c,$pageNumber,$txt);
	}
	private function cmdPlayers(CommandSender $c, $pageNumber) {
		$all = [];
		foreach ($this->owner->getServer()->getOnlinePlayers() as $p) {
			$all[$p->getName()] = mc::_("*current-server*");
		}
		foreach ($this->owner->getModule("ServerList")->getIds() as $id) {
			$dat = $this->owner->getModule("ServerList")->getServer($id);
			$host = $dat["host"];
			$port = $dat["port"];

			$Query = new MinecraftQuery( );
			try {
				$Query->Connect( $host, $port, 1 );
			} catch (MinecraftQueryException $e) {
				$this->owner->getLogger()->warning(mc::_("Query %1% failed: %2%",$host,$e->getMessage()));
				continue;
			}
			if (($players = $Query->GetPlayers()) === false) continue;
			if (count($players) == 0) continue;
			foreach ($players as $p) {
				if ($c->hasPermission("gb.cmd.query.players.showip")) {
					$all[$p] = "$id ($host:$port)";
				} else {
					$all[$p] = "$id";
				}
			}
		}
		if (count($all) == 0) {
			$c->sendMessage(TextFormat::YELLOW."Nobody is on-line at the moment");
		}
		$txt = [ mc::n(mc::_("One player found"),
										mc::_("%1% players found",count($all)),
										count($all)) ];
		ksort($all, SORT_NATURAL);
		foreach ($all as $i=>$j) {
			$txt[] = $i." @ ".$j;
		}
		return $this->paginateText($c,$pageNumber,$txt);
	}
	private function cmdListAll(CommandSender $c, $pageNumber) {
		$all = [];

		$dat = [
			"Players" => count($this->owner->getServer()->getOnlinePlayers()),
			"MaxPlayers" => $this->owner->getServer()->getMaxPlayers(),
			"List" => [],
		];
		foreach ($this->owner->getServer()->getOnlinePlayers() as $p) {
			$dat["List"][] = $p->getName();
		}
		$totals = [
			"Players"=>$dat["Players"],
			"MaxPlayers"=>$dat["MaxPlayers"],
		];
		$all[mc::_("**this-server**")] = $dat;
		foreach ($this->owner->getModule("ServerList")->getIds() as $id) {
			$dat = $this->owner->getModule("ServerList")->getServer($id);
			$host = $dat["host"];
			$port = $dat["port"];

			$Query = new MinecraftQuery( );
			try {
				$Query->Connect( $host, $port, 1 );
			} catch (MinecraftQueryException $e) {
				$this->owner->getLogger()->warning(mc::_("Query %1% failed: %2%",$host,$e->getMessage()));
				continue;
			}
			if (($info = $Query->GetInfo()) === false) continue;
			foreach (["Players","MaxPlayers"] as $i) {
				if (isset($info[$i])) $totals[$i] += $info[$i];
			}
			$all[$id] = [
				"Players" => $info["Players"],
				"MaxPlayers" => $info["MaxPlayers"],
				"List" => $Query->getPlayers(),
			];
		}
		$txt = [ mc::_("Totals: %1%/%2%", $totals["Players"], $totals["MaxPlayers"]) ];
		foreach ($all as $id=>$dat) {
			$txt[] = TextFormat::YELLOW.mc::_("%1% (%2%/%3%):", $id, $dat["Players"], $dat["MaxPlayers"]);
			if (!is_array($dat["List"])) continue;

			$cols = 8;
			$i = 0;
			foreach ($dat["List"] as $n) {
				if (($i++ % $cols) == 0) {
					$txt[] = $n;
				} else {
					$txt[count($txt)-1] .= ", ".$n;
				}
			}

		}

		return $this->paginateText($c,$pageNumber,$txt);
	}
	private function cmdSummary(CommandSender $c) {
		$all = [
			"servers" => 1,
			"on-line" => 1,
			"Players" => count($this->owner->getServer()->getOnlinePlayers()),
			"MaxPlayers" => $this->owner->getServer()->getMaxPlayers(),
		];
		foreach ($this->owner->getModule("ServerList")->getIds() as $id) {
			$dat = $this->owner->getModule("ServerList")->getServer($id);
			$host = $dat["host"];
			$port = $dat["port"];

			$all["servers"]++;

			$Query = new MinecraftQuery( );
			try {
				$Query->Connect( $host, $port, 1 );
			} catch (MinecraftQueryException $e) {
				$this->owner->getLogger()->warning(mc::_("Query %1% failed: %2%",$host,$e->getMessage()));
				continue;
			}
			if (($info = $Query->GetInfo()) === false) continue;
			$all["on-line"]++;
			foreach (["Players","MaxPlayers"] as $i) {
				if (isset($info[$i])) $all[$i] += $info[$i];
			}
		}
		$c->sendMessage(TextFormat::BLUE.mc::_("Network Status"));
		$c->sendMessage(TextFormat::GREEN.mc::_("Servers:%3% %1%/%2%",
																						$all["on-line"],$all["servers"],TextFormat::YELLOW));
		$c->sendMessage(TextFormat::GREEN.mc::_("Players:%3% %1%/%2%",
																						$all["Players"],$all["MaxPlayers"],TextFormat::YELLOW));
		return true;
	}
}
