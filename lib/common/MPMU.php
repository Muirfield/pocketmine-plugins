<?php
namespace aliuly\common;
use pocketmine\item\Item;

abstract class MPMU {
	// My PocketMine Utils
	static protected $items = [];
	const VERSION = "0.0.0";

	static public function plugin() {
		for ($x = __DIR__; $x != "." && $x != "/" ; $x = dirname($x)) {
			if (file_exists($x."/plugin.yml")) return basename($x);
		}
		return __DIR__;
	}
	static public function version($version = "") {
		if ($version == "") return self::VERSION;
		return self::apiCheck(self::VERSION,$version);
	}
	static public function pmCheck($server,$version = "") {
		if ($version == "") return $server->getApiVersion();
		return self::apiCheck($server->getApiVersion(),$version);
	}
	static public function apiCheck($api,$version) {
		switch (substr($version,0,2)) {
			case ">=":
				return version_compare($api,trim(substr($version,2))) >= 0;
			case "<=":
				return version_compare($api,trim(substr($version,2))) <= 0;
			case "<>":
			case "!=":
				return version_compare($api,trim(substr($version,2))) != 0;
		}
		switch (substr($version,0,1)) {
			case "=":
				return version_compare($api,trim(substr($version,1))) == 0;
			case "!":
			case "~":
				return version_compare($api,trim(substr($version,1))) != 0;
			case "<":
				return version_compare($api,trim(substr($version,1))) < 0;
			case ">":
				return version_compare($api,trim(substr($version,1))) > 0;
		}
		return version_compare($api,$version) >= 0;
	}
	static public function itemName(Item $item) {
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
	static public function gamemodeStr($mode) {
		if (class_exists(__NAMESPACE__."\\mc",false)) {
			switch ($mode) {
				case 0: return mc::_("Survival");
				case 1: return mc::_("Creative");
				case 2: return mc::_("Adventure");
				case 3: return mc::_("Spectator");
			}
			return mc::_("%1%-mode",$mode);
		}
		switch ($mode) {
			case 0: return "Survival";
			case 1: return "Creative";
			case 2: return "Adventure";
			case 3: return "Spectator";
		}
		return "$mode-mode";
	}
}
