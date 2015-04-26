<?php
/**
 ** OVERVIEW:Entity Management
 **
 ** COMMANDS
 **
 ** * entities : entity management
 **   usage: **entities** _[subcommand_ _[options]_
 **
 **   By default it will show the current entities.  The following
 **   sub-commands are available:
 **   - **entities** **ls** _[world]_
 **      - Show entities in _[world]_ (or current world if not specified).
 **   - **entities** **tiles** _[world]_
 **      - Show tile entities in _[world]_ (or current world if not specified).
 **   - **entities** **info** _[e#|t#]_
 **     - Show details about one or more entities or tiles.
 **   - **entities** **rm** _[e#]_
 **     - Removes one or more entities.
 **   - **entities** **sign**_N_ _[t#]_ _message text_
 **     - Changes the text line _N_ in the tile/sign identified by _t#_.
 **   - **entities** **count**
 **     - Show a count of the number of entities on the server.
 **   - **entities** **nuke** _[all|mobs|others]_
 **     -Clear entities from the server.
 **
 **/
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\Player;
use pocketmine\entity\Living;
use pocketmine\entity\Human;
use pocketmine\entity\Creature;
use pocketmine\tile\Sign;


class CmdEntities extends BaseCommand {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("entities",
							  ["description" => "Manage entities",
								"usage" => "/entities [tile|info|rm|sign#|count|nuke] [args]",
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
			$c->sendMessage("Usage: /et ls [world]");
			return false;
		}
		if (count($args)) {
			$level = $this->owner->getServer()->getLevelByName($args[0]);
			if (!$level) {
				$c->sendMessage("$args[0]: World not found");
				return true;
			}
		} else {
			if ($this->inGame($c,false)) {
				$level = $c->getLevel();
			} else {
				$level = $this->owner->getServer()->getDefaultLevel();
			}
		}

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
	private function cmdTileList(CommandSender $c,$args) {
		$pageNumber = $this->getPageNumber($args);
		if (count($args) > 1) {
			$c->sendMessage("Usage: /et tiles [world]");
			return false;
		}
		if (count($args)) {
			$level = $this->owner->getServer()->getLevelByName($args[0]);
			if (!$level) {
				$c->sendMessage("$args[0]: World not found");
				return true;
			}
		} else {
			if ($this->inGame($c,false)) {
				$level = $c->getLevel();
			} else {
				$level = $this->owner->getServer()->getDefaultLevel();
			}
		}

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
					$c->sendMessage("Invalid option");
					return false;
			}
		} elseif (count($args) != 0) {
			$c->sendMessage("NUKE Options: all, mobs, others");
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
		if ($mcnt) $c->sendMessage("Removed $mcnt mobs");
		if ($ecnt) $c->sendMessage("Removed $ecnt entities");
		if ($mcnt == 0 && $ecnt == 0) $c->sendMessage("Nothing was deleted");
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
		$c->sendMessage("Players: $humans");
		$c->sendMessage("Mobs:    $mobs");
		$c->sendMessage("Others:  $others");
		$c->sendMessage("Tiles:   $tiles");
		return true;
	}
	private function cmdEtInfo(CommandSender $c,$args) {
		$pageNumber = $this->getPageNumber($args);
		if (count($args) == 0) {
			$c->sendMessage("Usage: /et info [ids]");
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
				$txt[] = "Entity: $i";
				foreach ($this->dumpNbt($et->namedtag) as $ln) {
					$txt[] = $ln;
				}
				continue;
			}
			$tile = $this->getTile($i);
			if ($tile !== null) {
				++$cnt;
				$txt[] = "Tile: $i";
				foreach ($this->dumpNbt($tile->namedtag) as $ln) {
					$txt[] = $ln;
				}
				continue;
			}
			$c->sendMessage("Id:$i not found");
		}
		if (count($args) > 1) {
			$txt[0] = "$cnt Entities";
		}
		return $this->paginateText($c,$pageNumber,$txt);
	}
	private function cmdEtRm(CommandSender $c,$args) {
		if (count($args) == 0) {
			$c->sendMessage("Usage: /et rm [ids]");
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
				$et->close();
				continue;
			}
			$c->sendMessage("Id:$i not found");
		}
		if (count($lst)) {
			$c->sendMessage("Removed ".count($lst)." items: ".
								 implode(", ",$lst));
		} else {
			$c->sendMessage("No items removed");
		}
		return true;
	}

	private function cmdEtSign(CommandSender $c,$opt,$args) {
		if (count($args) < 1) {
			$c->sendMessage("Usage: /et sign[1-4] <id> <text>\n");
			return false;
		}
		$i = array_shift($args);
		$tile = $this->getTile($i);
		if ($tile == null) {
			$c->sendMessage("Tile $i not found");
			return true;
		}
		if (strtolower(substr($i,0,1)) != "t") {
			$c->sendMessage("Only applies to tile ids");
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

}
