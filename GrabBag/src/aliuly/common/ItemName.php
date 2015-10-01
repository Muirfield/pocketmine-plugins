<?php
namespace aliuly\common;
use pocketmine\item\Item;

/**
 * ItemName database
 */
abstract class ItemName {
	/** @var array $xnames extended names */
	static protected $xnames = null;
	/** @var str[] $items Nice names for items */
	static protected $items = [];
	/** @var str[] $usrnames Possibly localized names for items */
	static protected $usrnames = [];
	/**
	 * Initialize $usrnames
	 * @param str[] $names - names to load
	 */
	static public function initUsrNames(array $names) {
		self::$usrnames = $names;
	}
	/**
	 * Load the specified item names.
	 * Return number of items read, -1 in case of error.
	 * @param str $f - Filename to load
	 * @return int
	 */
	public static function loadUsrNames($f) {
		$tx = file($f, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
		$i = 0;
		if ($tx === false) return -1;
		foreach ($tx as $x) {
			$x = trim($x);
			if (substr($x,0,1) == "#" || substr($x,0,1) == ";") continue;
			$x = preg_split('/\s*=\s*/',$x,2);
			if (count($x) != 2) continue;
			++$i;
			self::$usrnames[$x[0]] = $x[1];
		}
		return $i;
	}

	/**
	 * Initialize extended names table
	 */
	static protected function initXnames() {
		self::$xnames = [
			Item::DYE => [
				0 => "Ink Sac",
				1 => "Rose Red",
				2 => "Cactus Green",
				3 => "Cocoa Beans",
				4 => "Lapis Lazuli",
				5 => "Purple Dye",
				6 => "Cyan Dye",
				7 => "Light Gray Dye",
				8 => "Gray Dye",
				9 => "Pink Dye",
				10 => "Lime Dye",
				11 => "Dandelion Yellow",
				12 => "Light Blue Dye",
				13 => "Magenta Dye",
				14 => "Orange Dye",
				15 => "Bone Meal",
				"*" => "Dye",
			],
			Item::SPAWN_EGG => [
				"*" => "Spawn Egg",
				32 => "Spawn Zombie",
				33 => "Spawn Creeper",
				34 => "Spawn Skeleton",
				35 => "Spawn Spider",
				36 => "Spawn Zombie Pigman",
				37 => "Spawn Slime",
				38 => "Spawn Enderman",
				39 => "Spawn Silverfish",
				40 => "Spawn Cave Spider",
				41 => "Spawn Ghast",
				42 => "Spawn Magma Cube",
				10 => "Spawn Chicken",
				11 => "Spawn Cow",
				12 => "Spawn Pig",
				13 => "Spawn Sheep",
				14 => "Spawn Wolf",
				16 => "Spawn Mooshroom",
				17 => "Spawn Squid",
				19 => "Spawn Bat",
				15 => "Spawn Villager",
			]
		];
	}

	/**
	 * Given an pocketmine\item\Item object, it returns a friendly name
	 * for it.
	 *
	 * @param Item item
	 * @return str
	 */
	static public function str(Item $item) {
		$id = $item->getId();
		$meta = $item->getDamage();
		if (isset(self::$usrnames[$id.":".$meta])) return self::$usrnames[$id.":".$meta];
		if (isset(self::$usrnames[$id])) return self::$usrnames[$id];
		if (self::$xnames == null) self::initXnames();

		if (isset(self::$xnames[$id])) {
			if (isset(self::$xnames[$id][$meta])) {
				return self::$xnames[$id][$meta];
			} elseif (isset(self::$xnames[$id]["*"])) {
				return self::$xnames[$id]["*"];
			} else {
				return self::$xnames[$id][0];
			}
		}
		$n = $item->getName();
		if ($n != "Unknown") return $n;
		if (count(self::$items) == 0) {
			$constants = array_keys((new \ReflectionClass("pocketmine\\item\\Item"))->getConstants());
			foreach ($constants as $constant) {
				$cid = constant("pocketmine\\item\\Item::$constant");
				$constant = str_replace("_", " ", $constant);
				self::$items[$cid] = $constant;
			}
		}
		if (isset(self::$items[$id])) return self::$items[$id];
		return $n;
	}
}
