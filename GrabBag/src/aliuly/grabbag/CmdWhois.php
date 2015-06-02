<?php
/**
 ** OVERVIEW:Informational
 **
 ** COMMANDS
 **
 ** * whois : Gives detail information on players
 **   usage: **whois** _<player>_
 **
 **/
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\Player;
use pocketmine\utils\TextFormat;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;

class CmdWhois extends BasicCli implements CommandExecutor {

	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("whois",
							  ["description" => mc::_("show players detail info"),
								"usage" => "/whois <player>",
								"permission" => "gb.cmd.whois"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "whois") return false;
		$pageNumber = $this->getPageNumber($args);
		if (count($args) != 1) {
			$sender->sendMessage(mc::_("You must specify a player's name"));
			return true;
		}
		$target = $this->owner->getServer()->getPlayer($args[0]);
		if($target == null) {
			$sender->sendMessage(mc::_("%1% can not be found.",$args[0]));
			return true;
		}
		$txt = [];
		$txt[] = TextFormat::AQUA.mc::_("About %1%",$args[0]);
		$txt[] = TextFormat::GREEN.mc::_("Health: ").TextFormat::WHITE
				 ."[".$target->getHealth()."/".$target->getMaxHealth()."]";
		$txt[] = TextFormat::GREEN.mc::_("World: ").TextFormat::WHITE
				 .$target->getLevel()->getName();

		$txt[] = TextFormat::GREEN.mc::_("Location: ").TextFormat::WHITE."X:".floor($target->getPosition()->x)." Y:".floor($target->getPosition()->y)." Z:".floor($target->getPosition()->z);
		if ($sender->hasPermission("gb.cmd.whois.showip"))
			$txt[] = TextFormat::GREEN.mc::_("IP Address: ").TextFormat::WHITE.$target->getAddress();
		$txt[] = TextFormat::GREEN.mc::_("Gamemode: ").TextFormat::WHITE
				 .MPMU::gamemodeStr($target->getGamemode());
		$txt[] = TextFormat::GREEN.mc::_("Whitelisted: ").TextFormat::WHITE
				 . ($target->isWhitelisted() ? "YES" : "NO");
		$txt[] = TextFormat::GREEN.mc::_("Opped: ").TextFormat::WHITE
				 . ($target->isOp() ? "YES" : "NO");
		$txt[] = TextFormat::GREEN.mc::_("Dislay Name: ").TextFormat::WHITE
				 . $target->getDisplayName();
		$txt[] = TextFormat::GREEN.mc::_("Flying: ").TextFormat::WHITE
				 . ($target->isOnGround() ? "NO" : "YES");

		$pm = $this->owner->getServer()->getPluginManager();
		if (($kr = $pm->getPlugin("KillRate")) !== null) {
			if (version_compare($kr->getDescription()->getVersion(),"1.1") >= 0) {
				$score = $kr->getScore($target);
				if ($score)
					$txt[] = TextFormat::GREEN.mc::_("KillRate Score: ").TextFormat::WHITE.$score;
			} else {
				$txt[] = TextFormat::RED.mc::_("KillRate version is too old (%1%)",
														 $kr->getDescription()->getVersion());
			}
		}

		if(($money = $pm->getPlugin("PocketMoney"))
			|| ($money = $pm->getPlugin("GoldStd"))
			|| ($money = $pm->getPlugin("EconomyAPI"))
			|| ($money = $pm->getPlugin("MassiveEconomy"))){
			switch($money->getName()){
				case "GoldStd":
					$money = $money->getMoney($target->getName());
					break;
				case "PocketMoney":
				case "MassiveEconomy":
					$money = $money->getMoney($target->getName());
					break;
				case "EconomyAPI":
					$money = $money->mymoney($target->getName());
					break;
				default:
					$money = false;
					break;
			}
			if ($money)
				$txt[]=TextFormat::GREEN.mc::_("Money: ").TextFormat::WHITE.$money;
		}
		return $this->paginateText($sender,$pageNumber,$txt);
	}
}
