<?php
namespace aliuly\livesigns;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;

use pocketmine\level\Position;
use aliuly\livesigns\common\mc;
use aliuly\livesigns\common\MPMU;
use aliuly\livesigns\common\BasicCli;


class LsCmds extends BasicCli {
	static public function isAssoc($arr) {
		return array_keys($arr) !== range(0, count($arr) - 1);
	}
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableSCmd("cfg",["usage" => mc::_("[id]"),
										 "help" => mc::_("List configured ids, and its contents"),
										 "permission" => "livesigns.cmd.info",
										 "aliases" => ["list","ls"]]);
		$this->enableSCmd("show",["usage" => mc::_("[id]"),
										  "help" => mc::_("Show fetched text"),
										  "permission" => "livesigns.cmd.info"]);
		$this->enableSCmd("reload",["usage" => "",
										  "help" => mc::_("Reload sign configuration"),
										  "permission" => "livesigns.cmd.update"]);
		$this->enableSCmd("update",["usage" => mc::_("<id>"),
										  "help" => mc::_("Retrieve sign id data"),
										  "permission" => "livesigns.cmd.update"]);
		$this->enableSCmd("announce",["usage" => mc::_("<id>"),
										  "help" => mc::_("Broadcast sign text"),
										  "permission" => "livesigns.cmd.broadcast"]);
		$this->enableSCmd("status",["usage" => "",
										  "help" => mc::_("LiveSigns tasks status"),
										  "permission" => "livesigns.cmd.info"]);
		$this->enableSCmd("set",["usage" => mc::_("<id> <type> <content>"),
										  "help" => mc::_("Add/Modify livesign"),
										  "permission" => "livesigns.cmd.addrm"]);
		$this->enableSCmd("rm",["usage" => mc::_("<id>"),
										  "help" => mc::_("remove livesigns"),
										  "permission" => "livesigns.cmd.addrm"]);
	}
	public function onSCommand(CommandSender $c,Command $cc,$scmd,$data,array $args) {
		switch($scmd) {
			case "cfg":
				return $this->cmdList($c,$args);
			case "show":
				return $this->cmdShow($c,$args);
			case "reload":
				if (count($args) != 0) return false;
				$c->sendMessage(mc::_("Reloading signs"));
				$this->owner->loadSigns();
				$this->owner->expireSign(null);
				$this->owner->scheduleRetrieve();
				return true;
			case "update":
				if (count($args) == 0) return false;
				foreach($args as $id) {
					$c->sendMessage(mc::_("Expiring %1%",$id));
					$this->owner->expireSign($id);
				}
				$this->owner->scheduleRetrieve();
				return true;
			case "announce":
				if (count($args) == 0) return false;
				$stx = $this->owner->getSignTxt();
				foreach ($args as $id) {
					if (!isset($stx[$id])) {
						$c->sendMessage(mc::_("LiveSign:%1% does not exist",$id));
						continue;
					}
					foreach ($stx[$id]["text"] as $ln) {
						$this->owner->getServer()->broadcastMessage($ln);
					}
				}
				return true;
			case "status":
				return $this->cmdStatus($c,$args);
			case "set":
				if (count($args) < 3) return false;
				$id = array_shift($args);
				$type = array_shift($args);
				$content = implode(" ",$args);
				$this->owner->loadSigns();
				$this->owner->updateSignCfg($id,$type,$content);
				$this->owner->saveSigns();
				$this->owner->expireSign($id);
				$this->owner->scheduleRetrieve();
				$c->sendMessage(mc::_("LiveSign: Created text %1% (%2%)",$id,$type));
				return true;
			case "rm":
				if (count($args) != 1) return false;
				$c->sendMessage(mc::_("LiveSign: Removing text %1%",$args[0]));
				$this->owner->loadSigns();
				$this->owner->updateSignCfg($args[0],null,null);
				$this->owner->saveSigns();
				$this->owner->scheduleRetrieve();
				return true;
		}
		return false;
	}
	private function cmdStatus(CommandSender $c,array $args){
		$pageNumber = $this->getPageNumber($args);
		$txt = $this->owner->getStats();
		return $this->paginateText($c,$pageNumber,$txt);
	}
	private function cmdShow(CommandSender $c,array $args){
		$pageNumber = $this->getPageNumber($args);
		$cfg = $this->owner->getSignCfg();
		$stx = $this->owner->getSignTxt();
		if (count($stx) == 0) {
			$c->sendMessage(mc::_("LiveSigns: Text cache is empty!"));
			return true;
		}
		if (count($args) == 0) {
			$txt = [ mc::_("LiveSigns texts: %1%",count($stx)) ];
			foreach (array_keys($cfg) as $id) {
				$txt[] = TextFormat::WHITE.$id.": ".(
					isset($stx[$id]) ? TextFormat::GREEN.mc::_("Available") .(
						isset($stx[$id]["datetime"]) ? "" :
						TextFormat::YELLOW.mc::_("Expired")
					) : TextFormat::RED.mc::_("Missing")
				);
			}
			return $this->paginateText($c,$pageNumber,$txt);
		}
		$txt = [];
		$count = 0;
		foreach ($args as $id) {
			if (!isset($stx[$id])) {
				$c->sendMessage(TextFormat::RED.mc::_("%1% not found",$id));
				continue;
			}
			++$count;
			$txt[] = TextFormat::AQUA.mc::_("LiveSign: ").TextFormat::WHITE.$id;
			foreach ($stx[$id]["text"] as $k) {
				$txt[] = TextFormat::AQUA."-   -".TextFormat::WHITE.$k;
			}
			if (isset($stx[$id]["datetime"])) {
				$txt[] = TextFormat::AQUA.mc::_("-    tstamp: ").
						 date(mc::_("Y-m-d H:i:s"),$stx[$id]["datetime"]);
			}
		}
		if (count($txt) == 0) {
			$c->sendMessage(TextFormat::RED.mc::_("No matches"));
			return true;
		}
		if ($count > 1) {
			array_unshift($txt,mc::_("LiveSigns %1%", $count));
		}
		return $this->paginateText($c,$pageNumber,$txt);
	}
	private function cmdList(CommandSender $c,array $args){
		$pageNumber = $this->getPageNumber($args);
		$signs = $this->owner->getSignCfg();
		if (count($args) == 0) {
			$txt = [ mc::n(mc::_("One LiveSign configured"),
								mc::_("%1% LiveSigns configured",count($signs)),
								count($signs)) ];
			$cols = 8;
			$i = 0;
			foreach (array_keys($signs) as $n) {
				if (($i++ % $cols) == 0) {
					$txt[] = $n;
				} else {
					$txt[count($txt)-1] .= ", ".$n;
				}
			}
			return $this->paginateText($c,$pageNumber,$txt);
		}
		$txt = [];
		$count = 0;
		foreach ($args as $id) {
			if (!isset($signs[$id])) {
				$c->sendMessage(TextFormat::RED.mc::_("%1% not found",$id));
				continue;
			}
			++$count;
			$txt[] = TextFormat::AQUA.mc::_("LiveSign: ").TextFormat::WHITE.$id;
			foreach (["type","content"] as $tag) {
				if (isset($signs[$id][$tag])) continue;
				$txt[] = TextFormat::AQUA.mc::_("-Type: ").TextFormat::RED.
						 mc::_("*MISSING*");
			}
			foreach ($signs[$id] as $tag=>$val) {
				if (is_array($val)) {
					$txt[] = TextFormat::AQUA."-$tag: ";
					if (self::isAssoc($val)) {
						foreach ($val as $j=>$k) {
							$txt[] = TextFormat::AQUA."-   $j:".TextFormat::WHITE.$k;
						}
					} else {
						foreach ($val as $k) {
							$txt[] = TextFormat::AQUA."-   -".TextFormat::WHITE.$k;
						}
					}
				} else {
					$txt[] = TextFormat::AQUA."-$tag: ".TextFormat::WHITE.$val;
				}
			}
		}
		if (count($txt) == 0) {
			$c->sendMessage(TextFormat::RED.mc::_("No matches"));
			return true;
		}
		if ($count > 1) {
			array_unshift($txt,mc::_("LiveSigns %1%", $count));
		}
		return $this->paginateText($c,$pageNumber,$txt);
	}
}
