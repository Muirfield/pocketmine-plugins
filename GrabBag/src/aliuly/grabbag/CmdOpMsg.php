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

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;

class CmdOpMsg extends BasicCli implements CommandExecutor,Listener {
	protected $rpt;

	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("opms",
							  ["description" => mc::_("Send message to ops"),
								"usage" => mc::_("/opms <message>"),
								"permission" => "gb.cmd.opms"]);
		$this->enableCmd("rpt",
							  ["description" => mc::_("Report issues to ops"),
								"usage" => mc::_("/rpt [message|read|clear <all|##>]"),
								"permission" => "gb.cmd.rpt"]);
		$this->rpt = new Config($this->owner->getDataFolder()."reports.yml",
										Config::YAML,[0,[]]);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		list($id,$rpt) = $this->rpt->getAll();
		if (count($rpt))
			$this->owner->getLogger()->info(
				TextFormat::RED.
				mc::n(mc::_("One report on file"),
						mc::_("%1% reports on file",count($rpt)),
						count($rpt)));
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		switch($cmd->getName()) {
			case "opms":
				if (count($args) == 0) return false;
				$ms = TextFormat::BLUE.mc::_("OpMsg [%1%] ",$sender->getName()).
					 TextFormat::YELLOW.implode(" ",$args);
				$this->owner->getLogger()->info($ms);
				$count = 0;
				foreach ($this->owner->getServer()->getOnlinePlayers() as $pl) {
					if (!$pl->isOp()) continue;
					$pl->sendMessage($ms);
					++$count;
				}
				if (($sender instanceof Player) && !$sender->isOp()) {
					if($count){
						$sender->sendMessage(mc::_("(ops:%1%) ",$count).implode(" ",$args));
					}else{
						$sender->sendMessage(mc::_("Message sent to console only"));
						if ($sender->hasPermission("gb.cmd.rpt")) {
							$sender->sendMessage(mc::_("Try /rpt instead"));
						}
					}
				}
				return true;
			case "rpt":
				if (count($args) == 0) return false;
				list($id,$rpt) = $this->rpt->getAll();
				if ($args[0] == "read" && (count($args) == 1 ||
													(count($args) == 2 && is_numeric($args[1])))) {
					if (!MPMU::access($sender,"gb.cmd.rpt.read")) return false;
					if (count($rpt) == 0) {
						$sender->sendMessage(TextFormat::RED.mc::_("No reports on file!"));
						return true;
					}
					$pageNumber = $this->getPageNumber($args);
					$tab = [[ mc::_("ID"),mc::_("Date"),mc::_("Name"),
								 mc::_("Reports: %1%",count($rpt)) ]];
					foreach ($rpt as $i=>$ln) {
						list($tm,$name,$ms) = $ln;
						$tm = date(mc::_("d-M H:i"),$tm);
						$tab[] = [ $i,$tm,$name,$ms ];
					}
					$this->paginateTable($sender,$pageNumber,$tab);
					return true;
				}
				if ($args[0] == "clear" && count($args) == 2) {
					if (!MPMU::access($sender,"gb.cmd.rpt.read")) return false;
					if ($args[1] == "all") {
						$rpt = [];
						$sender->sendMessage(TextFormat::RED.mc::_("All reports deleted"));
					} else {
						$i = intval($args[1]);
						if (!isset($rpt[$i])) {
							$sender->sendMessage(mc::_("Unknown report #%1%",$i));
							return true;
						}
						unset($rpt[$i]);
						$sender->sendMessage(mc::_("Deleting report #%1%",$i));
					}
				} else {
					$rpt[++$id] = [time(),$sender->getName(),implode(" ",$args)];
					$sender->sendMessage(mc::_("Report filed as #%1%",$id));
					$ms = TextFormat::BLUE.
						 mc::_("Rpt[#%1% from %2%] ",$id,$sender->getName()).
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
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
		if (!$pl->hasPermission("gb.cmd.rpt.read")) return;
		list($id,$rpt) = $this->rpt->getAll();
		if (count($rpt)) $pl->sendMessage(
			mc::n(mc::_("One report on file"),
					mc::_("%1% reports on file",count($rpt)),
					count($rpt)));
	}
}
