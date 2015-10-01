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

use aliuly\common\BasicCli;
use aliuly\common\mc;
use aliuly\common\MPMU;
use aliuly\common\PermUtils;

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
	private function queryServer($id) {
		$lst = $this->owner->getModule("ServerList");
		$cc = [];
		if ($this->owner->getModule("query-task") && !$lst->getServerAttr($id,"no-query-task",false)) {
			// Using query daemon cached data...
			foreach (["info","players"] as $dd) {
				$cc[$dd] = $lst->getQueryData($id,"query.".$dd);
				if ($cc[$dd] === null)
				  unset($cc[$dd]);
				else {
					if (isset($cc[$dd]["age"])) unset($cc[$dd]["age"]);
				}
			}
		}
		if (count($cc) == 0) {
			$host = $lst->getServerAttr($id,"query-host");
			$port = $lst->getServerAttr($id,"port");

			$Query = new MinecraftQuery( );
			try {
				$Query->Connect( $host, $port, 1 );
			} catch (MinecraftQueryException $e) {
				return null;
			}
			$cc["info"] = $Query->GetInfo();
			$cc["players"] = $Query->GetPlayers();
			foreach (["info","players"] as $dd) {
				if ($cc[$dd] === false) unset($cc[$dd]);
			}
		}
		return $cc;
	}

	private function cmdQuery(CommandSender $c,$q,$args,$pageNumber = -1) {
		if ($pageNumber == -1) $pageNumber = $this->getPageNumber($args);
		if (count($args) != 1) {
			$c->sendMessage(TextFormat::RED.mc::_("Usage: %1% <id>",$q));
			return false;
		}
		$id = array_shift($args);
		$lst = $this->owner->getModule("ServerList");
		if ($lst->getServer($id) === null) {
			$c->sendMessage(TextFormat::RED.mc::_("%1% does not exist",$id));
			return false;
		}
		$cc = $this->queryServer($id);
		if ($cc == null) {
			$c->sendMessage(TextFormat::RED.mc::_("Query %1% failed",$id));
			return true;
		}

		$txt = [ mc::_("[%2%] query for %1%", $id, $q) ];
		switch ($q) {
			case "info":
			  if (!isset($cc["info"])) {
					$c->sendMessage(TextFormat::RED.mc::_("Query of %1% returned no data", $id));
					return true;
				}
				foreach ($cc["info"] as $i=>$j) {
					if ($i == "RawPlugins") continue;
					if (is_array($j)) continue;
					$txt[] =  TextFormat::GREEN. $i.": ".TextFormat::WHITE.$j;
				}
				break;
			case "plugins":
				if (!isset($cc["info"])) {
					$c->sendMessage(TextFormat::RED.mc::_("Query of %1% returned no data", $id));
					return true;
				}
				if (!isset($cc["info"]["Plugins"]) || !is_array($cc["info"]["Plugins"])) {
					$c->sendMessage(TextFormat::RED.mc::_("%1%: No plugins", $id));
					return true;
				}
				$cols = 8;
				$i = 0;
				foreach ($cc["info"]["Plugins"] as $n) {
					if (($i++ % $cols) == 0) {
						$txt[] = $n;
					} else {
						$txt[count($txt)-1] .= ", ".$n;
					}
				}
				break;
			case "players":
				if (!isset($cc["players"])) {
					$c->sendMessage(TextFormat::RED.mc::_("Query of %1% returned no data", $id));
					return true;
				}
				if (count($cc["players"]) == 0) {
					$c->sendMessage(TextFormat::RED.mc::_("%1%: No players", $id));
					return true;
				}
				$cols = 8;
				$i = 0;
				foreach ($cc["players"] as $n) {
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
			$cc = $this->queryServer($id);
			if ($cc === null) continue;
			if (!isset($cc["players"])) continue;
			if (count($cc["players"]) == 0) continue;

			if ($c->hasPermission("gb.cmd.query.players.showip")) {
				$host = $this->owner->getModule("ServerList")->getServerAttr($id,"query-host");
				$port = $this->owner->getModule("ServerList")->getServerAttr($id,"port");

				$inf = "$id ($host:$port)";
			} else {
				$inf = $id;
			}
			foreach ($cc["players"] as $p) {
				$all[$p] = $inf;
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
			$cc = $this->queryServer($id);
			if ($cc === null) continue;
			if (!isset($cc["info"])) continue;

			foreach (["Players","MaxPlayers"] as $i) {
				if (isset($cc["info"][$i])) $totals[$i] += $cc["info"][$i];
			}
			$all[$id] = [
				"Players" => $cc["info"]["Players"],
				"MaxPlayers" => $cc["info"]["MaxPlayers"],
				"List" => isset($cc["players"]) ? $cc["players"] : null,
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
			$all["servers"]++;
			$cc = $this->queryServer($id);
			if ($cc === null) continue;
			if (!isset($cc["info"])) continue;
			$info = $cc["info"];
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
