<?php
//= cmd:entities,Entity_Management
//: entity management
//> usage: **entities** _[subcommand]_ _[options]_
//:
//: By default it will show the current entities.  The following
//: sub-commands are available:
//> - **entities** **ls** _[world]_
//:    - Show entities in _[world]_ (or current world if not specified).
//> - **entities** **tiles** _[world]_
//:    - Show tile entities in _[world]_ (or current world if not specified).
//> - **entities** **info** _[e#|t#]_
//:    - Show details about one or more entities or tiles.
//: - **entities** **rm** _[e#]_
//:    - Removes one or more entities.
//: - **entities** **sign**_N_ _[t#]_ _message text_
//:    - Changes the text line _N_ in the tile/sign identified by _t#_.
//: - **entities** **count**
//:    - Show a count of the number of entities on the server.
//: - **entities** **nuke** _[all|mobs|others]_
//:    - Clear entities from the server.
//:
//: Additionally, tiles can be specified by providing the following:
//:
//: - t(x),(y),(z)[,world]

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\math\Vector3;

use pocketmine\Player;
use pocketmine\entity\Living;
use pocketmine\entity\Human;
use pocketmine\entity\Creature;
use pocketmine\tile\Sign;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;
use aliuly\grabbag\common\ItemName;
use aliuly\grabbag\common\PermUtils;

class CmdEntities extends BasicCli implements CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.entities", "entity management", "op");
		$this->enableCmd("entities",
							  ["description" => mc::_("Manage entities"),
								"usage" => mc::_("/entities [tile|info|rm|sign#|count|nuke] [args]"),
								"aliases" => ["et"],
								"permission" => "gb.cmd.entities"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "entities") return false;
		if (count($args) == 0) $args = [ "ls" ];
		$scmd = strtolower(array_shift($args));

		switch ($scmd) {
			case "count":
				return $this->cmdCount($sender);
			case "nuke":
				return $this->cmdNuke($sender,$args);
			case "ls":
				return $this->cmdEtList($sender,$args);
			case "tiles":
			case "tile":
				return $this->cmdTileList($sender,$args);
			case "info":
			case "nbt":
				return $this->cmdEtInfo($sender,$args);
			case "rm":
				return $this->cmdEtRm($sender,$args);
			case "sign1":
			case "sign2":
			case "sign3":
			case "sign4":
				return $this->cmdEtSign($sender,$scmd,$args);
		}
		return false;
	}
	//////////////////////////////////////////////////////////////////////
	//
	// Support functions
	//
	//////////////////////////////////////////////////////////////////////
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
	private function getEntity($id) {
		if (strtolower(substr($id,0,1)) == "e") {
			$id = substr($id,1);
		}
		if (!is_numeric($id)) return null;
		$id = intval($id);
		foreach($this->owner->getServer()->getLevels() as $l) {
			$e = $l->getEntity($id);
			if ($e !== null) return $e;
		}
		return null;
	}
	private function getTile($id) {
		if (strtolower(substr($id,0,1)) == "t") {
			$id = substr($id,1);
		}
		if (preg_match('/^(\d+),(\d+),(\d+),(\S+)$/',$id,$mv)) {
			$l = $this->owner->getServer()->getLevelByName($mv[4]);
			if ($l === null) return null;
			$mv = new Vector3($mv[1],$mv[2],$mv[3]);
			return $l->getTile($mv);
		}
		if (preg_match('/^(\d+),(\d+),(\d+)$/',$id,$mv)) {
			$l = $this->owner->getServer()->getDefaultLevel();
			if ($l === null) return null;
			$mv = new Vector3($mv[1],$mv[2],$mv[3]);
			$e = $l->getTile($mv);
			if ($e !== null) return $e;
			foreach($this->owner->getServer()->getLevels() as $l) {
				$e = $l->getTile($mv);
				if ($e !== null) return $e;
			}
			return null;
		}
		if (!is_numeric($id)) return null;
		$id = intval($id);
		foreach($this->owner->getServer()->getLevels() as $l) {
			$e = $l->getTileById($id);
			if ($e !== null) return $e;
		}
		return null;
	}
	//////////////////////////////////////////////////////////////////////
	//
	// Sub commands
	//
	//////////////////////////////////////////////////////////////////////
	private function cmdEtList(CommandSender $c,$args) {
		$pageNumber = $this->getPageNumber($args);
		if (count($args) > 1) {
			$c->sendMessage(mc::_("Usage: /et ls [world]"));
			return false;
		}
		if (count($args)) {
			$level = $this->owner->getServer()->getLevelByName($args[0]);
			if (!$level) {
				$c->sendMessage(mc::_("%1%: World not found",$args[0]));
				return true;
			}
		} else {
			if (MPMU::inGame($c,false)) {
				$level = $c->getLevel();
			} else {
				$level = $this->owner->getServer()->getDefaultLevel();
			}
		}

		$cnt=0;
		$tab = [];
		$tab[] = ["-",mc::_("Name"),mc::_("Position"),mc::_("Health")];
		foreach ($level->getEntities() as $e) {
			if ($e instanceof Player) continue;
			$id = $e->getId();
			$pos = implode(",",[floor($e->getX()),floor($e->getY()),floor($e->getZ())]);
			if ($e instanceof Living) {
				$name = $e->getName();
			} elseif ($e instanceof \pocketmine\entity\Item) {
				$name = mc::_("Item:%1%",ItemName::str($e->getItem()));
			} else {
				$name = basename(strtr(get_class($e),"\\","/"));
			}
			++$cnt;
			$tab[] = [ $id,$name,$pos,$e->getHealth() ];
		}
		$tab[0][0] = "#:$cnt";
		if ($cnt) return $this->paginateTable($c,$pageNumber,$tab);
		$c->sendMessage(mc::_("No entities found"));
		return true;
	}
	private function cmdTileList(CommandSender $c,$args) {
		$pageNumber = $this->getPageNumber($args);
		if (count($args) > 1) {
			$c->sendMessage(mc::_("Usage: /et tiles [world]"));
			return false;
		}
		if (count($args)) {
			$level = $this->owner->getServer()->getLevelByName($args[0]);
			if (!$level) {
				$c->sendMessage(mc::_("%1%: World not found",$args[0]));
				return true;
			}
		} else {
			if (MPMU::inGame($c,false)) {
				$level = $c->getLevel();
			} else {
				$level = $this->owner->getServer()->getDefaultLevel();
			}
		}
		$cnt = 0;
		$tab = [];
		$tab[] = ["-",mc::_("Name"),mc::_("Position")];
		foreach ($level->getTiles() as $t) {
			$id = $t->getId();
			$pos = implode(",",[floor($t->getX()),floor($t->getY()),floor($t->getZ())]);
			$name = basename(strtr(get_class($t),"\\","/"));
			$tab[] = [ $id,$name,$pos ];
			++$cnt;
		}
		$tab[0][0] = "#:$cnt";
		if ($cnt) return $this->paginateTable($c,$pageNumber,$tab);
		$c->sendMessage(mc::_("No tiles found"));
		return true;
	}
	private function cmdNuke(CommandSender $c,$args) {
		$mobs = true;
		$ents = false;
		if (count($args) == 1) {
			switch(strtolower($args[0])) {
				case "others":
					$mobs = false;
					$ents = true;
					break;
				case "mobs":
					$mobs = true;
					$ents = false;
					break;
				case "all":
					$mobs = true;
					$ents = true;
					break;
				default:
					$c->sendMessage(mc::_("Invalid option"));
					return false;
			}
		} elseif (count($args) != 0) {
			$c->sendMessage(mc::_("NUKE Options: all, mobs, others"));
			return false;
		}
		$mcnt = $ecnt = 0;
		foreach($this->owner->getServer()->getLevels() as $l) {
			foreach($l->getEntities() as $e) {
				if($e instanceof Human) continue;
				if (($e instanceof Creature) && $mobs) {
					$mcnt++;
					$e->close();
					continue;
				}
				if ($ents) {
					$ecnt++;
					$e->close();
				}
			}
		}
		if ($mcnt) $c->sendMessage(mc::_("Removed %1% mobs",$mcnt));
		if ($ecnt) $c->sendMessage(mc::_("Removed %1% entities",$ecnt));
		if ($mcnt == 0 && $ecnt == 0) $c->sendMessage(mc::_("Nothing was deleted"));
		return true;
	}
	private function cmdCount(CommandSender $c) {
		$humans = 0;
		$mobs = 0;
		$others = 0;
		$tiles = 0;
		foreach ($this->owner->getServer()->getLevels() as $l) {
			foreach ($l->getEntities() as $e) {
				if ($e instanceof Human)
					++$humans;
				elseif($e instanceof Creature)
					++$mobs;
				else
					++$others;
			}
			$tiles += count($l->getTiles());
		}
		$c->sendMessage(mc::_("Players: %1%",$humans));
		$c->sendMessage(mc::_("Mobs:    %1%",$mobs));
		$c->sendMessage(mc::_("Others:  %1%",$others));
		$c->sendMessage(mc::_("Tiles:   %1%",$tiles));
		return true;
	}
	private function cmdEtInfo(CommandSender $c,$args) {
		$pageNumber = $this->getPageNumber($args);
		if (count($args) == 0) {
			$c->sendMessage(mc::_("Usage: /et info [ids]"));
			return false;
		}
		$cnt = 0;
		$txt = [];
		if (count($args) > 1) {
			$txt[] = "";
		}
		foreach ($args as $i) {
			$et = $this->getEntity($i);
			if ($et !== null) {
				$txt[] = mc::_("Entity: %1% (%2%)",$i,basename(strtr(get_class($et),"\\","/")));
				$et->saveNBT();
				foreach ($this->dumpNbt($et->namedtag) as $ln) {
					$txt[] = $ln;
				}
				continue;
			}
			$tile = $this->getTile($i);
			if ($tile !== null) {
				++$cnt;
				$txt[] = mc::_("Tile: %1%",$i);
				foreach ($this->dumpNbt($tile->namedtag) as $ln) {
					$txt[] = $ln;
				}
				continue;
			}
			$c->sendMessage(mc::_("Id:%1% not found",$i));
		}
		if (count($args) > 1) {
			$txt[0] = mc::_("%1% Entities",$cnt);
		}
		return $this->paginateText($c,$pageNumber,$txt);
	}
	private function cmdEtRm(CommandSender $c,$args) {
		if (count($args) == 0) {
			$c->sendMessage(mc::_("Usage: /et rm [ids]"));
			return false;
		}
		$lst = [];

		foreach ($args as $i) {
			$et = $this->getEntity($i);
			if ($et !== null) {
				$lst[] = $i;
				$et->close();
				continue;
			}
			$tile = $this->getTile($i);
			if ($tile !== null) {
				$lst[] = $i;
				$tile->close();
				continue;
			}
			$c->sendMessage(mc::_("Id:%1% not found",$i));
		}
		if (count($lst)) {
			$c->sendMessage(mc::_("Removed %1% items: %2%",count($lst),
										 implode(", ",$lst)));
		} else {
			$c->sendMessage(mc::_("No items removed"));
		}
		return true;
	}

	private function cmdEtSign(CommandSender $c,$opt,$args) {
		if (count($args) < 1) {
			$c->sendMessage(mc::_("Usage: /et sign[1-4] <id> <text>\n"));
			return false;
		}
		$i = array_shift($args);
		$tile = $this->getTile($i);
		if ($tile == null) {
			$c->sendMessage(mc::_("Tile %1% not found",$i));
			return true;
		}
		if (strtolower(substr($i,0,1)) != "t") {
			$c->sendMessage(mc::_("Only applies to tile ids"));
			return false;
		}
		if (!($tile instanceof Sign)) {
			$c->sendMessage(mc::_("Tile %1% is not a sign",$i));
			return false;
		}
		$sign = $tile->getText();
		$txt = implode(" ",$args);
		$sub = intval(substr($opt,-1)) - 1;
		if ($sign[$sub] == $txt) {
			$c->sendMessage(mc::_("Text unchanged"));
			return true;
		}
		$sign[$sub] = $txt;
		$tile->setText($sign[0],$sign[1],$sign[2],$sign[3]);
		$c->sendMessage(mc::_("Changed to \"%1%\"",$txt));
		return true;
	}

}
