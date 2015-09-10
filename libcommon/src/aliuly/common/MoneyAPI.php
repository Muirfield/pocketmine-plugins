<?php
//= api-features
//: - Multiple money support

namespace aliuly\common;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\IPlayer;
use LogLevel;


/**
 * This class allows you to use a number of miscellaneous Economy
 * plugins.
 */
abstract class MoneyAPI {
	/**
	 * Show a warning when the money API is missing
	 *
	 * @param PluginBase $plugin - current plugin
	 * @param LogLevel $level - optional log level
	 */
	static public function noMoney(PluginBase $plugin,$level = LogLevel::WARNING) {
		if (class_exists(__NAMESPACE__."\\mc",false)) {
			$plugin->getLogger()->error($level,TextFormat::RED.
											  mc::_("! MISSING MONEY API PLUGIN"));
			$plugin->getLogger()->error(TextFormat::BLUE.
											  mc::_(". Please install one of the following:"));
			$plugin->getLogger()->error(TextFormat::WHITE.
											  mc::_("* GoldStd"));
			$plugin->getLogger()->error(TextFormat::WHITE.
											  mc::_("* PocketMoney"));
			$plugin->getLogger()->error(TextFormat::WHITE.
											  mc::_("* EconomyAPI or"));
			$plugin->getLogger()->error(TextFormat::WHITE.
											  mc::_("* MassiveEconomy"));
		} else {
			$plugin->getLogger()->error($level,TextFormat::RED.
											  "! MISSING MONEY API PLUGIN");
			$plugin->getLogger()->error(TextFormat::BLUE.
											  ". Please install one of the following:");
			$plugin->getLogger()->error(TextFormat::WHITE.
											  "* GoldStd");
			$plugin->getLogger()->error(TextFormat::WHITE.
											  "* PocketMoney");
			$plugin->getLogger()->error(TextFormat::WHITE.
											  "* EconomyAPI or");
			$plugin->getLogger()->error(TextFormat::WHITE.
											  "* MassiveEconomy");
		}
	}
	/**
	 * Show a notice when the money API is found
	 *
	 * @param PluginBase $plugin - current plugin
	 * @param PluginBase $api - found plugin
	 * @param LogLevel $level - optional log level
	 */
	static public function foundMoney(PluginBase $plugin,$api,$level = LogLevel::INFO) {
		if (class_exists(__NAMESPACE__."\\mc",false)) {
			$plugin->getLogger()->log($level,TextFormat::BLUE.
											  mc::_("Using money API from %1%",
													  $api->getFullName()));
		} else {
			$plugin->getLogger()->log($level,TextFormat::BLUE.
											  "Using money API from ".$api->getFullName());
		}
	}
	/**
	 * Find a supported *money* plugin
	 *
	 * @param var obj - Server or Plugin object
	 * @return null|Plugin
	 */
	static public function moneyPlugin($obj) {
		if ($obj instanceof Server) {
			$server = $obj;
		} else {
			$server = $obj->getServer();
		}
		$pm = $server->getPluginManager();
		if(!($money = $pm->getPlugin("PocketMoney"))
			&& !($money = $pm->getPlugin("GoldStd"))
			&& !($money = $pm->getPlugin("EconomyAPI"))
			&& !($money = $pm->getPlugin("MassiveEconomy"))){
			return null;
		}
		return $money;
	}
	/**
	 * Gives money to a player.
	 *
	 * @param Plugin api Economy plugin (from moneyPlugin)
	 * @param str|IPlayer p Player to pay
	 * @param int money Amount of money to play (can be negative)
	 *
	 * @return bool
	 */
	static public function grantMoney($api,$p,$money) {
		if(!$api) return false;
		switch($api->getName()){
			case "GoldStd": // takes IPlayer|str
				$api->grantMoney($p, $money);
				break;
			case "PocketMoney": // takes str
			  if ($p instanceof IPlayer) $p = $p->getName();
				$api->grantMoney($p, $money);
				break;
			case "EconomyAPI": // Takes str
				if ($p instanceof IPlayer) $p = $p->getName();
				$api->setMoney($p,$api->mymoney($p)+$money);
				break;
			case "MassiveEconomy": // Takes str
				if ($p instanceof IPlayer) $p = $p->getName();
				$api->payPlayer($p->getName(),$money);
				break;
			default:
				return false;
		}
		return true;
	}
	/**
	 * Gets player balance
	 *
	 * @param Plugin $api Economy plugin (from moneyPlugin)
	 * @param str|IPlayer $player Player to lookup
	 *
	 * @return int
	 */
	static public function getMoney($api,$player) {
		if(!$api) return false;
		switch($api->getName()){
			case "GoldStd":
				return $api->getMoney($player);
				break;
			case "PocketMoney":
			case "MassiveEconomy":
				if ($player instanceof IPlayer) $player = $player->getName();
				return $api->getMoney($player);
			case "EconomyAPI":
				if ($player instanceof IPlayer) $player = $player->getName();
				return $api->mymoney($player);
			default:
				return false;
				break;
		}
	}
}
