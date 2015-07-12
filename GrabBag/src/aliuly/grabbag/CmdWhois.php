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
use aliuly\grabbag\common\MoneyAPI;

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
			$target = $this->owner->getServer()->getOfflinePlayer($args[0]);
			if ($target == null || !$target->hasPlayedBefore()) {
				$sender->sendMessage(mc::_("%1% can not be found.",$args[0]));
				return true;
			}
		}
		$txt = [];
		$txt[] = TextFormat::AQUA.mc::_("About %1%",$args[0]);

		$txt[] = TextFormat::GREEN.mc::_("On-Line: ").TextFormat::WHITE
					 . ($target->isOnline() ? mc::_("YES") : mc::_("NO"));

		if ($target instanceof Player) {
			$txt[] = TextFormat::GREEN.mc::_("Health: ").TextFormat::WHITE
					 ."[".$target->getHealth()."/".$target->getMaxHealth()."]";
			$txt[] = TextFormat::GREEN.mc::_("World: ").TextFormat::WHITE
					 .$target->getLevel()->getName();

			$txt[] = TextFormat::GREEN.mc::_("Location: ").TextFormat::WHITE."X:".floor($target->getPosition()->x)." Y:".floor($target->getPosition()->y)." Z:".floor($target->getPosition()->z);
			if ($sender->hasPermission("gb.cmd.whois.showip"))
				$txt[] = TextFormat::GREEN.mc::_("IP Address: ").TextFormat::WHITE.$target->getAddress();
			$txt[] = TextFormat::GREEN.mc::_("Gamemode: ").TextFormat::WHITE
					 .MPMU::gamemodeStr($target->getGamemode());
			$txt[] = TextFormat::GREEN.mc::_("Display Name: ").TextFormat::WHITE
					 . $target->getDisplayName();
			$txt[] = TextFormat::GREEN.mc::_("Flying: ").TextFormat::WHITE
					 . ($target->isOnGround() ? mc::_("NO") : mc::_("YES"));
			//1.5
			if (MPMU::apiVersion("1.12.0")) {
				$txt[] = TextFormat::GREEN.mc::_("UUID: ").TextFormat::WHITE
						 . $target->getUniqueId();
				$txt[] = TextFormat::GREEN.mc::_("ClientID: ").TextFormat::WHITE
						 . $target->getClientId();
				$txt[] = TextFormat::GREEN.mc::_("Can Fly: ").TextFormat::WHITE
						 . ($target->getAllowFlight() ? mc::_("YES") : mc::_("NO") );

			}

		} else {
			$txt[] = TextFormat::GREEN.mc::_("Banned: ").TextFormat::WHITE
					 . ($target->isBanned() ? mc::_("YES") : mc::_("NO"));
		}
		$txt[] = TextFormat::GREEN.mc::_("Whitelisted: ").TextFormat::WHITE
				 . ($target->isWhitelisted() ? mc::_("YES") : mc::_("NO"));
		$txt[] = TextFormat::GREEN.mc::_("Opped: ").TextFormat::WHITE
				 . ($target->isOp() ? mc::_("YES") : mc::_("NO"));

		$txt[] = TextFormat::GREEN.mc::_("First Played: ").TextFormat::WHITE
				 . date(mc::_("d-M-Y H:i"),$target->getFirstPlayed()/1000);
		// $target->getLastPlayed()."\n";//##DEBUG
		if ($target->getLastPlayed()) {
			$txt[] = TextFormat::GREEN.mc::_("Last Played: ").TextFormat::WHITE
					 . date(mc::_("d-M-Y H:i"),$target->getLastPlayed()/1000);
		}

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
		if (($sa = $pm->getPlugin("SimpleAuth")) !== null) {
			if ($target instanceof Player) {
				$txt[] = TextFormat::GREEN.mc::_("Authenticated: ").TextFormat::WHITE
						 . ($sa->isPlayerAuthenticated($target) ? mc::_("YES") : mc::_("NO"));
			}
			$txt[] = TextFormat::GREEN.mc::_("Registered: ").TextFormat::WHITE
					 . ($sa->isPlayerRegistered($target) ? mc::_("YES") : mc::_("NO"));
		}
		$money = MoneyAPI::moneyPlugin($this->owner);
		if ($money !== null) {
			$txt[]=TextFormat::GREEN.mc::_("Money: ").TextFormat::WHITE.
				MoneyAPI::getMoney($money,$target->getName()).
				TextFormat::AQUA.mc::_(" (from %1%)",$money->getFullName());
		}
		return $this->paginateText($sender,$pageNumber,$txt);
	}
}
