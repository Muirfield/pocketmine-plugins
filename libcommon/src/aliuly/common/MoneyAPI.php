<?php
namespace aliuly\common;
use pocketmine\Server;

/**
 * This class allows you to use a number of miscellaneous Economy
 * plugins.
 */
abstract class MoneyAPI {
	/**
	 * Find a supported *money* plugin
	 *
	 * @param Server server PocketMine server object
	 * @return null|Plugin
	 */
	static public function moneyPlugin(Server $server) {
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
	 * @param Player p Player to pay
	 * @param int money Amount of money to play (can be negative)
	 *
	 * @return bool
	 */
	static public function grantMoney($api,$p,$money) {
		if(!$api) return false;
		switch($api->getName()){
			case "GoldStd":
				$api->grantMoney($p, $money);
				break;
			case "PocketMoney":
				$api->grantMoney($p, $money);
				break;
			case "EconomyAPI":
				$api->setMoney($p,$api->mymoney($p)+$money);
				break;
			case "MassiveEconomy":
				$api->payPlayer($p,$money);
				break;
			default:
				return false;
		}
		return true;
	}
	/**
	 * Gets player balance
	 *
	 * @param Plugin api Economy plugin (from moneyPlugin)
	 * @param Player p Player to lookup
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
				return $api->getMoney($player);
			case "EconomyAPI":
				return $api->mymoney($player);
			default:
				return false;
				break;
		}
	}
}
