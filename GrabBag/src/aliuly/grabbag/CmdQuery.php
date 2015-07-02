<?php
/**
 ** OVERVIEW:Server Management
 **
 ** COMMANDS
 **
 ** * query : query remote servers
 **   usage: **query** **[add|rm|ls|info|plugins|players|summary]** _[opts]_
 **
 **   This is a query client that you can use to query other
 **   remote servers.  Options:
 **   - **query add** _&lt;id&gt;_ _&lt;address&gt;_ _&lt;port&gt;_ _[comments]_
 **     - adds a `query` connection with `id`.
 **   - **query rm** _&lt;id&gt;_
 **     - Removes `query` connection `id`.
 **   - **query ls**
 **     - List configured `query` connections.
 **   - **rcon** _&lt;id&gt;_ _&lt;command&gt;_
 **     - Sends the `command` to the connection `id`.
 **
 ** CONFIG:query-hosts
 **
 ** This section configures the query connections.  You can configure
 ** this section through the *query* command.
 **/
namespace aliuly\grabbag;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;
use xPaw\MinecraftQuery;
use xPaw\MinecraftQueryException;

class CmdQuery extends BasicCli implements CommandExecutor {
	protected $servers;

	public function __construct($owner,$cfg) {
		parent::__construct($owner);
		$this->servers = $cfg;
		$this->enableCmd("query",
							  ["description" => mc::_("Query servers"),
								"usage" => mc::_("/query [add|rm|ls|info|plugins|players|summary] <opts>"),
								"permission" => "gb.cmd.query"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if (count($args) == 0) $args = ["summary"];
		switch($cmd->getName()) {
			case "query":
				switch ($n = strtolower(array_shift($args))) {
					case "add":
						if (!MPMU::access($sender,"gb.cmd.query.addrm")) return true;
						return $this->cmdAdd($sender,$args);
					case "rm":
						if (!MPMU::access($sender,"gb.cmd.query.addrm")) return true;
						return $this->cmdRm($sender,$args);
					case "ls":
						return $this->cmdList($sender,$args);
					case "info":
					case "plugins":
						return $this->cmdQuery($sender,$n,$args);
					case "players":
						$pageNumber = $this->getPageNumber($args);
					  if (count($args)) return $this->cmdQuery($sender,$n,$args,$pageNumber);
						return $this->cmdPlayers($sender,$pageNumber);
					case "summary":
					  return $this->cmdSummary($sender);
				}
		}
		return false;
	}
	private function cmdAdd(CommandSender $c,$args) {
		if (count($args) < 2) {
			$c->sendMessage(TextFormat::RED.mc::_("Usage: add <id> <host> [port [comments]]"));
			return false;
		}
		if (count($args) == 2) array_push($args,19132); // Add default port if needed
		$id = array_shift($args);
		if (isset($this->servers[$id])) {
			$c->sendMessage(TextFormat::RED.mc::_("%1% is an id that is already in use.",$id));
			$c->sendMessage(mc::_("Use rm first"));
			return false;
		}
		$this->servers[$id] = implode(" ",$args);
		$this->owner->cfgSave("query-hosts",$this->servers);
		$c->sendMessage(mc::_("Query id %1% configured",$id));
		return true;
	}
	private function cmdRm(CommandSender $c,$args) {
		if (count($args) != 1) {
			$c->sendMessage(TextFormat::RED.mc::_("Usage: rm <id>"));
			return false;
		}
		$id = array_shift($args);
		if (!isset($this->servers[$id])) {
			$c->sendMessage(TextFormat::RED.mc::_("%1% does not exist",$id));
			return false;
		}
		unset($this->servers[$id]);
		$this->owner->cfgSave("query-hosts",$this->servers);
		$c->sendMessage(mc::_("Query id %1% deleted",$id));
		return true;
	}
	private function cmdList(CommandSender $c,$args) {
		$pageNumber = $this->getPageNumber($args);
		if (count($args) != 0) return false;
		$txt = ["Query connections"];

		foreach ($this->servers as $id => $dat) {
			$dat = preg_split('/\s+/',$dat,3);
			$host = array_shift($dat);
			$port = array_shift($dat);
			$ln = count($dat) ? " #".$dat[0] : "";
			$txt[] = "$id: $host:$port$ln";
		}
		return $this->paginateText($c,$pageNumber,$txt);
	}
	private function cmdQuery(CommandSender $c,$q,$args,$pageNumber = -1) {
		if ($pageNumber == -1) $pageNumber = $this->getPageNumber($args);
		if (count($args) != 1) {
			$c->sendMessage(TextFormat::RED.mc::_("Usage: %1% <id>",$q));
			return false;
		}
		$id = array_shift($args);
		if (!isset($this->servers[$id])) {
			$c->sendMessage(TextFormat::RED.mc::_("%1% does not exist",$id));
			return false;
		}
		$dat = preg_split('/\s+/',$this->servers[$id],3);
		$host = array_shift($dat);
		$port = array_shift($dat);

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
		foreach ($this->servers as $id=>$ln) {
			$dat = preg_split('/\s+/',$ln,3);
			$host = array_shift($dat);
			$port = array_shift($dat);

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
				$all[$p] = "$id ($host:$port)";
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
	private function cmdSummary(CommandSender $c) {
		$all = [
			"servers" => 1,
			"on-line" => 1,
			"Players" => count($this->owner->getServer()->getOnlinePlayers()),
			"MaxPlayers" => $this->owner->getServer()->getMaxPlayers(),
		];
		foreach ($this->servers as $id=>$ln) {
			$all["servers"]++;
			$dat = preg_split('/\s+/',$ln,3);
			$host = array_shift($dat);
			$port = array_shift($dat);

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
