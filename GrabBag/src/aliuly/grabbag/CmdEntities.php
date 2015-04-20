<?php
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\Player;
use pocketmine\entity\Living;
use pocketmine\tile\Sign;


class CmdEntities extends BaseCommand {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("entities",
							  ["description" => "Manage entities",
								"usage" => "/entities [tile|info|rm|sign#] [args]",
								"aliases" => ["et"],
								"permission" => "gb.cmd.entities"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "entities") return false;

		$pageNumber = $this->getPageNumber($args);
		$level = null;
		if (isset($args[0])) {
			$level = $this->owner->getServer()->getLevelByName($args[0]);
			if ($level) array_shift($args);
		}
		if (!$level) {
			if (!$this->inGame($sender)) return false;
			$level = $sender->getLevel();
		}
		if (count($args)) {
			$sub = strtolower(array_shift($args));
			switch ($sub) {
				case "tiles":
				case "tile":
					return $this->cmdTileList($sender,$level,$pageNumber);
				case "info":
				case "nbt":
					return $this->cmdEtInfo($sender,$level,$args,$pageNumber);
				case "rm":
					return $this->cmdEtRm($sender,$level,$args);
				case "sign1":
				case "sign2":
				case "sign3":
				case "sign4":
					return $this->cmdEtSign($sender,$level,$sub,$args);
			}
			return false;
		}
		return $this->cmdEtList($sender,$level,$pageNumber);
	}
	private function cmdTileList(CommandSender $c,$level,$pageNumber) {
		$tab = [];
		$tab[] = [$level->getName(),"Name","Position"];
		foreach ($level->getTiles() as $t) {
			$id = $t->getId();
			$pos = implode(",",[floor($t->getX()),floor($t->getY()),floor($t->getZ())]);
			$name = basename(strtr(get_class($t),"\\","/"));
			$tab[] = [ $id,$name,$pos ];
		}
		return $this->paginateTable($c,$pageNumber,$tab);
	}
	private function cmdEtList(CommandSender $c,$level,$pageNumber) {
		$tab = [];
		$tab[] = [$level->getName(),"Name","Position","Health"];
		foreach ($level->getEntities() as $e) {
			if ($e instanceof Player) continue;
			$id = $e->getId();
			$pos = implode(",",[floor($e->getX()),floor($e->getY()),floor($e->getZ())]);
			if ($e instanceof Living) {
				$name = $e->getName();
			} elseif ($e instanceof \pocketmine\entity\Item) {
				$name = "Item:".$this->itemName($e->getItem());
			} else {
				$name = basename(strtr(get_class($e),"\\","/"));
			}
			$tab[] = [ $id,$name,$pos,$e->getHealth() ];
		}
		return $this->paginateTable($c,$pageNumber,$tab);
	}
	private function cmdEtInfo(CommandSender $c,$level,$args,$pageNumber) {
		$cnt = 0;
		if (count($args) == 0) return false;
		$txt = [];
		if (count($args) > 1) {
			$txt[] = "";
		}
		foreach ($args as $i) {
			if (strtolower(substr($i,0,1)) == "t") {
				$i = substr($i,1);
				if (!is_numeric($i)) {
					$c->sendMessage("Invalid Tile id $i");
					continue;
				}
				$tile = $level->getTileById(intval($i));
				if ($tile == null) {
					$c->sendMessage("Tile $i not found");
					continue;
				}
				++$cnt;
				$txt[] = "Tile: $i";
				foreach ($this->dumpNbt($tile->namedtag) as $ln) {
					$txt[] = $ln;
				}
			} else {
				if (strtolower(substr($i,0,1)) == "e") {
					$i = substr($i,1);
				}
				if (!is_numeric($i)) {
					$c->sendMessage("Invalid Entity id $i");
					continue;
				}
				$et = $level->getEntity(intval($i));
				if ($et == null) {
					$c->sendMessage("Entity $i not found");
					continue;
				}
				++$cnt;
				$txt[] = "Entity: $i";
				foreach ($this->dumpNbt($et->namedtag) as $ln) {
					$txt[] = $ln;
				}
			}
		}
		if (count($args) > 1) {
			$txt[0] = "$cnt Entities";
		}
		return $this->paginateText($c,$pageNumber,$txt);
	}
	private function cmdEtRm(CommandSender $c,$level,$args) {
		$cnt = 0;
		if (count($args) == 0) return false;
		foreach ($args as $i) {
			if (strtolower(substr($i,0,1)) == "t") {
				$i = substr($i,1);
				if (!is_numeric($i)) {
					$c->sendMessage("Invalid Tile id $i");
					continue;
				}
				$tile = $level->getTileById(intval($i));
				if ($tile == null) {
					$c->sendMessage("Tile $i not found");
					continue;
				}
				++$cnt;
				$tile->close();
				continue;
			}
			if (strtolower(substr($i,0,1)) == "e") {
				$i = substr($i,1);
			}
			if (!is_numeric($i)) {
				$c->sendMessage("Invalid Entity id $i");
				continue;
			}
			$et = $level->getEntity(intval($i));
			if ($et == null) {
				$c->sendMessage("Entity $i not found");
				continue;
			}
			++$cnt;
			$et->close();
		}
		if ($cnt) {
			$c->sendMessage("Removed entities: ".$cnt);
		}
		return true;
	}
	private function cmdEtSign(CommandSender $c,$level,$sub,$args) {
		if (count($args) < 1) return false;
		$i = array_shift($args);
		if (strtolower(substr($i,0,1)) != "t") {
			$c->sendMessage("Only applies to tile ids");
			return false;
		}
		$i = substr($i,1);
		if (!is_numeric($i)) {
			$c->sendMessage("Invalid Tile id $i");
			return false;
		}
		$tile = $level->getTileById(intval($i));
		if ($tile == null) {
			$c->sendMessage("Tile $i not found");
			return false;
		}
		if (!($tile instanceof Sign)) {
			$c->sendMessage("Tile $i is not a sign");
			return false;
		}
		$sign = $tile->getText();
		$txt = implode(" ",$args);
		$sub = intval(substr($sub,-1)) - 1;
		if ($sign[$sub] == $txt) {
			$c->sendMessage("Text unchanged");
			return true;
		}
		$sign[$sub] = $txt;
		$tile->setText($sign[0],$sign[1],$sign[2],$sign[3]);
		$c->sendMessage("Changed to \"$txt\"");
		return true;
	}

	private function dumpNbtIndent($spc,&$off,&$last) {
		if (isset($off[$spc])) return $off[$spc];
		$last += 2;
		$off[$spc] = str_repeat(' ',$last);
		return $off[$spc];
	}
	public function dumpNbt($nbt) {
		$txt = [];
		$name = '';
		$off=[];
		$last = 0;

		foreach (explode("\n",print_r($nbt,true)) as $ln) {
			if (trim($ln) == "(" || trim($ln) == ")" || trim($ln) == "") continue;
			if (preg_match('/^(\s*)(\[[^\]]+\])\s*=>\s*pocketmine\\\\nbt\\\\tag\\\\(Enum|Compound)/',$ln,$m)) {
				$txt[] = ".".$this->dumpNbtIndent($m[1],$off,$last).$m[2];
				continue;
			}
			if (preg_match('/^\s*\[name:protected\]\s*=>\s*(.*)$/',$ln,$m)) {
				$name = $m[1];
			}
			if (preg_match('/^(\s*)\[value:protected\]\s*=>\s*(.*)$/',$ln,$m)) {
				if ($m[2] == "Array") continue;
				$txt[] = ".".$this->dumpNbtIndent($m[1],$off,$last).$name.": ".
						 $m[2];
				$name = "";
			}
		}
		return $txt;
	}
}
