<?php
/**
 ** OVERVIEW:Server Management
 **
 ** COMMANDS
 **
 ** * opms : sends a message to ops only
 **   usage: **opms** _[msg]_
 **
 **   Sends chat messages that are only see by ops.  Only works with ops
 **   that are on-line at the moment.  If you no ops are on-line you
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
 **   The following ops only commands are available:
 **   - **rpt** **read** _[##]_
 **     - reads reports.  You can specify the page by specifying a number.
 **   - **rpt** **clear** _<all|##>_
 **     - will delete the specified report or if `all`, all the reports.
 **
 **/

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;

class CmdOpMsg extends BaseCommand implements Listener {
	protected $rpt;

	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("opms",
							  ["description" => "Send message to ops",
								"usage" => "/opms <message>",
								"permission" => "gb.cmd.opms"]);
		$this->enableCmd("rpt",
							  ["description" => "Report issues ops",
								"usage" => "/rpt [message|read|clear <all|##>]",
								"permission" => "gb.cmd.rpt"]);
		$this->rpt = new Config($this->owner->getDataFolder()."reports.yml",
										Config::YAML,[0,[]]);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		list($id,$rpt) = $this->rpt->getAll();
		if (count($rpt))
			$this->owner->getLogger()->info(TextFormat::RED.
													  count($rpt)." reports on file");
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		switch($cmd->getName()) {
			case "opms":
				if (count($args) == 0) return false;
				$ms = TextFormat::BLUE.
					 "OpMsg [".$sender->getName()."] ".TextFormat::YELLOW.
					 implode(" ",$args);
				$this->owner->getLogger()->info($ms);
				$count = 0;
				foreach ($this->owner->getServer()->getOnlinePlayers() as $pl) {
					if (!$pl->isOp()) continue;
					$pl->sendMessage($ms);
					++$count;
				}
				if (($sender instanceof Player) && !$sender->isOp()) {
					if($count){
						$sender->sendMessage("(ops:$count) ".implode(" ",$args));
					}else{
						$sender->sendMessage("Message sent to console only");
						if ($sender->hasPermission("gb.cmd.rpt")) {
							$sender->sendMessage("Try /rpt instead");
						}
					}
				}
				return true;
			case "rpt":
				if (count($args) == 0) return false;
				list($id,$rpt) = $this->rpt->getAll();
				if ($args[0] == "read" && (count($args) == 1 ||
													(count($args) == 2 && is_numeric($args[1])))) {
					if (!$this->access($sender,"gb.cmd.rpt.read")) return false;
					if (count($rpt) == 0) {
						$sender->sendMessage(TextFormat::RED."No reports on file!");
						return true;
					}
					$pageNumber = $this->getPageNumber($args);
					$tab = [[ "ID","Date","Name","Reports: ".count($rpt) ]];
					foreach ($rpt as $i=>$ln) {
						list($tm,$name,$ms) = $ln;
						$tm = date("d-M H:i",$tm);
						$tab[] = [ $i,$tm,$name,$ms ];
					}
					$this->paginateTable($sender,$pageNumber,$tab);
					return true;
				}
				if ($args[0] == "clear" && count($args) == 2) {
					if (!$this->access($sender,"gb.cmd.rpt.read")) return false;
					if ($args[1] == "all") {
						$rpt = [];
					} else {
						$i = intval($args[1]);
						if (!isset($rpt[$i])) {
							$sender->sendMessage("Unknown report #$i");
							return true;
						}
						unset($rpt[$i]);
						$sender->sendMessage("Deleting report #$i");
					}
				} else {
					$rpt[++$id] = [time(),$sender->getName(),implode(" ",$args)];
					$sender->sendMessage("Report filed as #".$id);
					$ms = TextFormat::BLUE.
						 "Rpt[#$id from ".$sender->getName()."] ".
						 TextFormat::YELLOW.implode(" ",$args);
					$this->owner->getLogger()->info($ms);
					foreach ($this->owner->getServer()->getOnlinePlayers() as $pl) {
						if (!$pl->isOp()) continue;
						$pl->sendMessage($ms);
					}
				}
				$this->rpt->setAll([$id,$rpt]);
				$this->rpt->save();
				return true;
		}
		return false;
	}
	public function onPlayerJoin(PlayerJoinEvent $e) {
		$pl = $e->getPlayer();
		if ($pl == null) return;
		if (!$pl->hasPermission("gb.cmd.rpt.read")) return;
		list($id,$rpt) = $this->rpt->getAll();
		if (count($rpt)) $pl->sendMessage(count($rpt)." reports on file");
	}
}
