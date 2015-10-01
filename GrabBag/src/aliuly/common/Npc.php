<?php
namespace aliuly\common;

use pocketmine\entity\Human;
use pocketmine\level\Location;

use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Tag;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Float;
use pocketmine\nbt\tag\String;
use pocketmine\nbt\tag\Int;

/**
 * This class implements an NPC player
 */
class Npc extends Human {
	/**
	 * Spawns an NPC in game
	 * @param str $name - diplay name for this mob
	 * @param Location $pos - where to place this Mob
	 * @param str $classname - class to create
	 */
	static public function spawnNpc($name,Location $pos,$classname,$opts=null){
		if ($opts == null) $opts = [];
		if (!isset($opts["skin"])) {
			$opts["skin"] =
				str_repeat("\xFF", 32*16*2) . str_repeat("\xFF", 32*16*2) .
				str_repeat("\xFF", 32*16*2) . str_repeat("\xFF", 32*16*2) .
				str_repeat("\x80", 32*16*2) . str_repeat("\x80", 32*16*2) .
				str_repeat("\x80", 32*16*2) . str_repeat("\x80", 32*16*2);
		}
		if (!isset($opts["slim"])) $opts["slim"] = false;
		$ndat = [];
		$ndat["Pos"] = new Enum("Pos", [
			new Double("", $pos->x),
			new Double("", $pos->y),
			new Double("", $pos->z)]);
		if (isset($opts["motion"])) {
			$ndat["Motion"] = new Enum("Motion", [
				new Double("",$opts["motion"][0]),
				new Double("",$opts["motion"][1]),
				new Double("",$opts["motion"][2])]);
			unset($opts["motion"]);
		} else {
			$ndat["Motion"] = new Enum("Motion", [
				new Double("",0),
				new Double("",0),
				new Double("",0)]);
		}
		if (isset($opts["rotation"])) {
			$ndat["Rotation"] = new Enum("Rotation", [
				new Float("",$opts["rotation"][0]),
				new Float("",$opts["rotation"][1])]);
			unset($opts["rotation"]);
		} else {
			$ndat["Rotation"] = new Enum("Rotation", [
				new Float("",$pos->yaw),
				new Float("",$pos->pitch)]);
		}
		$ndat["Skin"] = new Compound("Skin", [
			"Data" => new String("Data", $opts["skin"]),
			"Slim" => new Byte("Slim", $opts["slim"] ? 1 : 0)]);
		unset($opts["skin"]);
		unset($opts["slim"]);

		foreach ($opts as $k=>$v) {
			if ($v instanceof Tag) {
				$ndat[$k] = $v;
				continue;
			}
			if (is_array($v) && count($v) == 2) {
				list($type,$value) = $v;
				$type = "pocketmine\\nbt\\tag\\".$type;
				$ndat[$k] = new $type($k,$value);
				continue;
			}
			switch (gettype($v)) {
				case "boolean":
					$ndat[$k] = new Byte($k,$v ? 1 : 0);
					break;
				case "integer":
					$ndat[$k] = new Int($k, $v);
					break;
				case "double":
					$ndat[$k] = new Double($k,$v);
					break;
				case "string":
					$ndat[$k] = new String($k,$v);
			}
		}
		$npc = new $classname($pos->getLevel()->getChunk($pos->getX()>>4,
																		 $pos->getZ()>>4),
									 new Compound("",$ndat));
		$npc->setNameTag($name);
		return $npc;
	}
}
