<?php
namespace aliuly\common;

use pocketmine\entity\Human;
use pocketmine\level\Location;

use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\EnumTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntTag;

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
		$ndat["Pos"] = new EnumTag("Pos", [
			new DoubleTag("", $pos->x),
			new DoubleTag("", $pos->y),
			new DoubleTag("", $pos->z)]);
		if (isset($opts["motion"])) {
			$ndat["Motion"] = new EnumTag("Motion", [
				new DoubleTag("",$opts["motion"][0]),
				new DoubleTag("",$opts["motion"][1]),
				new DoubleTag("",$opts["motion"][2])]);
			unset($opts["motion"]);
		} else {
			$ndat["Motion"] = new EnumTag("Motion", [
				new DoubleTag("",0),
				new DoubleTag("",0),
				new DoubleTag("",0)]);
		}
		if (isset($opts["rotation"])) {
			$ndat["Rotation"] = new EnumTag("Rotation", [
				new FloatTag("",$opts["rotation"][0]),
				new FloatTag("",$opts["rotation"][1])]);
			unset($opts["rotation"]);
		} else {
			$ndat["Rotation"] = new EnumTag("Rotation", [
				new FloatTag("",$pos->yaw),
				new FloatTag("",$pos->pitch)]);
		}
		$ndat["Skin"] = new CompoundTag("Skin", [
			"Data" => new StringTag("Data", $opts["skin"]),
			"Slim" => new ByteTag("Slim", $opts["slim"] ? 1 : 0)]);
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
					$ndat[$k] = new ByteTag($k,$v ? 1 : 0);
					break;
				case "integer":
					$ndat[$k] = new IntTag($k, $v);
					break;
				case "double":
					$ndat[$k] = new DoubleTag($k,$v);
					break;
				case "string":
					$ndat[$k] = new StringTag($k,$v);
			}
		}
		$npc = new $classname($pos->getLevel()->getChunk($pos->getX()>>4,
																		 $pos->getZ()>>4),
									 new CompoundTag("",$ndat));
		$npc->setNameTag($name);
		return $npc;
	}
}
