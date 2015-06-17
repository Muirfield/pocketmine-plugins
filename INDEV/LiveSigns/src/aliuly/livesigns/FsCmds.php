<?php
namespace aliuly\livesigns;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

use pocketmine\level\Position;
use pocketmine\math\Vector3;

use aliuly\livesigns\common\mc;
use aliuly\common\MPMU;
use aliuly\livesigns\common\BasicCli;


class FsCmds extends BasicCli {
	public function __construct($owner) {
		parent::__construct($owner);
		/*
		$this->enableSCmd("ftls",["usage"=> mc::_("[world]"),
										  "help" => mc::_("List floating signs"),
										  "permssion" => "livesigns.cmd.ftls"]);
		$this->enableSCmd("ftadd",["usage"=> mc::_("[x,y,z[:world]|player] <idtxt>"),
											"help" => mc::_("Add Floating Sign"),
											"permission" => "livesigns.cmd.addrm"]);
		$this->enableSCmd("ftrm",["usage"=> mc::_("[x,y,z[:world]|player [radius]|[world] id"),
											"help" => mc::_("Remove Floating Sign"),
											"permission" => "livesigns.cmd.addrm"]);
		*/
	}
	public function onSCmd(CommandSender $c,$args) {
		if (count($args) == 0) return false;
		$scmd = strtolower(array_shift($args));
		switch($scmd) {
			case "ls":
				$pageNumber = $this->getPageNumber($args);
				$cfg = $this->owner->getFloats()->getCfg();
				$pps = $this->owner->getFloats()->getParticles();
				if (count($args)) {
					$level = $this->owner->getServer()->getLevelByName($n = implode(" ",$args));
					if ($level == null) {
						$c->sendMessage(mc::_("%1% not found",$n));
						return true;
					}
				} else {
					if (MPMU::inGame($c,false)) {
						$level = $c->getLevel();
					} else {
						$level = $this->owner->getServer()->getDefaultLevel();
					}
				}
				$world = $level->getName();
				if (!isset($cfg[$world])) {
					$c->sendMessage(mc::_("No Floating Signs in %1%",$world));
					return true;
				}
				$txt = [ mc::n(mc::_("One configured sign in %1%",$world),
									mc::_("%1% configured signs in %2%",
											count($cfg[$world]),$world),
									count($cfg[$world])) ];
				foreach ($cfg[$world] as $id => $item) {
					$txt[] = TextFormat::GREEN."- ".$id." : ".(
						isset($pps[$world][$id]) ?
						TextFormat::BLUE.mc::_("Spawned") :
						TextFormat::WHITE.mc::_("N/A"));
				}
				return $this->paginateText($c,$pageNumber,$txt);
			case "add":
				if (count($args) == 0) return false;
				if (($pl=$this->owner->getServer()->getPlayer($args[0])) !== null){
					$pos = $pl;
					array_shift($args);
				} elseif (preg_match('/^(-?\d+),(-?\d+),(-?\d+)$/',$args[0],$mv)) {
					if (MPMU::inGame($c,false)) {
						$level = $c->getLevel();
					} else {
						$level = $this->owner->getServer()->getDefaultLevel();
					}
					$pos = new Position($mv[1],$mv[2],$mv[3],$level);
					array_shift($args);
				} elseif (preg_match('/^(-?\d+),(-?\d+),(-?\d+):(.+)$/',$args[0],$mv)) {
					$level = $this->owner->getServer()->getLevelByName($mv[4]);
					if ($level == null) {
						$c->sendMessage(mc::_("%1% not found",$mv[4]));
						return true;
					}
					$pos = new Position($mv[1],$mv[2],$mv[3],$level);
					array_shift($args);
				} else {
					if (MPMU::inGame($c,false)) {
						$pos = $c;
					} else {
						$c->sendMessage(mc::_("You must be in-game or specify a position"));
						return true;
					}
				}
				$text = implode(" ",$args);
				$cfg = $this->owner->getSignCfg();
				if (!isset($cfg[$text])) {
					$c->sendMessage(mc::_("%1% is not a configured text id",$text));
					return true;
				}
				$c->sendMessage(mc::_("Adding floating text sign"));
				$this->owner->getFloats()->addFloat($pos,$text,null);
				return true;
			case "rm":
				if (count($args) == 0) return false;
				$cfg = $this->owner->getFloats()->getCfg();
				$signs = $this->owner->getSignCfg();
				if (isset($cfg[$args[0]]) && isset($signs[$args[1]])) {
					if (count($args) != 2) return false;
					return $this->idRm($c,$args[0],$args[1]);
				}
				if (isset($signs[$args[0]])) {
					if (count($args) != 1) return false;
					if (MPMU::inGame($c,false)) {
						$level = $c->getLevel();
					} else {
						$level = $this->owner->getServer()->getDefaultLevel();
					}
					return $this->idRm($c,$level->getName(),$args[0]);
				}
				if (count($args) > 2) return false;
				/*
				if (count($args) == 2) {
					if (!is_numeric($args[1])) return false;
					$r = array_pop($args);
				} else {
					$r = 0;
					}*/
				if (count($args) != 1) return false;
				if (preg_match('/^(-?\d+),(-?\d+),(-?\d+)$/',$args[0],$mv)) {
					return $this->posRm($c,new Vector3($mv[1],$mv[2],$mv[3]),null);
				}
				if (preg_match('/^(-?\d+),(-?\d+),(-?\d+):(.+)$/',$args[0],$mv)) {
					if (!isset($cfg[$mv[4]])) {
						$c->sendMessage(mc::_("Unknown world %1%",$mv[4]));
						return true;
					}
					return $this->posRm($c,new Vector3($mv[1],$mv[2],$mv[3]),$mv[4]);
				}
				if (($pl=$this->owner->getServer()->getPlayer($args[0])) !== null) {
					return $this->posRm($c,new Vector3($pl->getFloorX(),$pl->getFloorY(),$pl->getFloorZ()),
											  $pl->getLevel()->getName());
				}
				break;
			default:
				$c->sendMessage(mc::_("Usage:"));
				$c->sendMessage(mc::_("/fs ls [world] - list worlds"));
				$c->sendMessage(mc::_("/fs add [x,y,z[:world]|player] <idtxt> - Add sign"));
				$c->sendMessage(mc::_("/fs rm <x,y,z[:world]|player|[world] idtxt> - Remove sign"));
		}
		return false;
	}
	private function posRm($c,$v3,$world) {
		if ($world == null) {
			if (MPMU::inGame($c,false)) {
				$world = $c->getLevel()->getName();
			} else {
				$world = $this->owner->getServer()->getDefaultLevel()->getName();
			}
		}

		$ids = [];
		$cfg = $this->owner->getFloats()->getCfg();
		if (!isset($cfg[$world])) {
			$c->sendMessage(mc::_("%1% not found",$world));
			return true;
		}
		$sel = implode(":",[$v3->getFloorX(),$v3->getFloorY(),$v3->getFloorZ()]);
		foreach ($cfg[$world] as $fid=>$dat) {
			if (implode(":",array_map("intval",$dat["pos"])) == $sel)
				$ids[] = $fid;
		}
		foreach ($ids as $j) {
			$c->sendMessage(mc::_("Deleting %1% from %2%",$j,$world));
			$this->owner->getFloats()->rmFloat($world,$j);
		}
		return true;
	}
	private function idRm($c,$world,$id) {
		$ids = [];
		$cfg = $this->owner->getFloats()->getCfg();
		if (!isset($cfg[$world])) {
			$c->sendMessage(mc::_("%1% not found",$world));
			return true;
		}
		foreach ($cfg[$world] as $fid=>$dat) {
			if ($dat["text"] == $id) $ids[] = $fid;
		}
		foreach ($ids as $j) {
			$c->sendMessage(mc::_("Deleting %1% from %2%",$j,$world));
			$this->owner->getFloats()->rmFloat($world,$j);
		}
		return true;
	}
}
