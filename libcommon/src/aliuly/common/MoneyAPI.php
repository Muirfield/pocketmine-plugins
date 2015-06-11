<?php
namespace aliuly\common;

abstract class MoneyAPI {
	//////////////////////////////////////////////////////////////////////
	//
	// Economy/Money handlers
	//
	//////////////////////////////////////////////////////////////////////
	static public function moneyPlugin($server) {
		$pm = $server->getPluginManager();
		if(!($money = $pm->getPlugin("PocketMoney"))
			&& !($money = $pm->getPlugin("GoldStd"))
			&& !($money = $pm->getPlugin("EconomyAPI"))
			&& !($money = $pm->getPlugin("MassiveEconomy"))){
			return null;
		}
		return $money;
	}
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
