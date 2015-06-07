<?php
namespace aliuly\mtp\common;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;
use pocketmine\utils\MainLogger;
use pocketmine\command\CommandSender;
use pocketmine\Player;

abstract class MPMU {
	// My PocketMine Utils
	static protected $items = [];
	const VERSION = "0.0.1";

	static public function version($version = "") {
		if ($version == "") return self::VERSION;
		return self::apiCheck(self::VERSION,$version);
	}
	static public function apiVersion($version = "") {
		if ($version == "") return \pocketmine\API_VERSION;
		return self::apiCheck(\pocketmine\API_VERSION,$version);
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
		if (intval($api) != intval($version)) return 0;
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

	static public function access(CommandSender $sender, $permission,$msg=true) {
		if($sender->hasPermission($permission)) return true;
		if ($msg)
			$sender->sendMessage(mc::_("You do not have permission to do that."));
		return false;
	}
	static public function inGame(CommandSender $sender,$msg = true) {
		if (!($sender instanceof Player)) {
			if ($msg) $sender->sendMessage(mc::_("You can only do this in-game"));
			return false;
		}
		return true;
	}
	static public function iName($player) {
		if ($player instanceof Player) {
			$player = strtolower($player->getName());
		}
		return $player;
	}
}
