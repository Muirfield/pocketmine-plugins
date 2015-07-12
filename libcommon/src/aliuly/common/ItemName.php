<?php
namespace aliuly\common;
use pocketmine\item\Item;
use aliuly\common\mc;

/**
 * ItemName database
 */
abstract class ItemName {
	/** @var array $xnames extended names */
	static protected $xnames = null;
	/** @var str[] $items Nice names for items */
	static protected $items = [];

	/**
	 * Initialize extended names table
	 */
	static protected function initXnames() {
		self::$xnames = [
			351 => [
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
			383 => [
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
		if (self::$xnames == null) {
			self::initXnames();
		}
		if (isset(self::$xnames[$item->getId()])) {
			if (isset(self::$xnames[$item->getId()][$item->getDamage()])) {
				return self::$xnames[$item->getId()][$item->getDamage()];
			} elseif (isset(self::$xnames[$item->getId()]["*"])) {
				return self::$xnames[$item->getId()]["*"];
			} else {
				return self::$xnames[$item->getId()][0];
			}
		}
		$n = $item->getName();
		if ($n != "Unknown") return $n;
		if (count(self::$items) == 0) {
			$constants = array_keys((new \ReflectionClass("pocketmine\\item\\Item"))->getConstants());
			foreach ($constants as $constant) {
				$id = constant("pocketmine\\item\\Item::$constant");
				$constant = str_replace("_", " ", $constant);
				self::$items[$id] = $constant;
			}
		}
		if (isset(self::$items[$item->getId()]))
			return self::$items[$item->getId()];
		return $n;
	}
}
