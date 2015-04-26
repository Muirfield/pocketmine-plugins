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

class CmdWhois extends BaseCommand {

	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("whois",
							  ["description" => "show players detail info",
								"usage" => "/whois <player>",
								"permission" => "gb.cmd.whois"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "whois") return false;
		$pageNumber = $this->getPageNumber($args);
		if (count($args) != 1) {
			$sender->sendMessage("You must specify a player's name");
			return true;
		}
		$target = $this->owner->getServer()->getPlayer($args[0]);
		if($target == null) {
			$sender->sendMessage($args[0]." can not be found.");
			return true;
		}
		$txt = [];
		$txt[] = TextFormat::AQUA."About $args[0]".TextFormat::RESET;
		$txt[] = TextFormat::GREEN."Health: ".TextFormat::WHITE
				 ."[".$target->getHealth()."/".$target->getMaxHealth()."]"
				 .TextFormat::RESET;
		$txt[] = TextFormat::GREEN."World: ".TextFormat::WHITE
				 .$target->getLevel()->getName().TextFormat::RESET;

		$txt[] = TextFormat::GREEN."Location: ".TextFormat::WHITE."X:".floor($target->getPosition()->x)." Y:".floor($target->getPosition()->y)." Z:".floor($target->getPosition()->z)."".TextFormat::RESET;
		if ($sender->hasPermission("gb.cmd.whois.showip"))
			$txt[] = TextFormat::GREEN."IP Address: ".TextFormat::WHITE.$target->getAddress().TextFormat::RESET;
		$txt[] = TextFormat::GREEN."Gamemode: ".TextFormat::WHITE
				 .$this->owner->gamemodeString($target->getGamemode())
				 .TextFormat::RESET;
		$txt[] = TextFormat::GREEN."Whitelisted: ".TextFormat::WHITE
				 . ($target->isWhitelisted() ? "YES" : "NO").TextFormat::RESET;
		$txt[] = TextFormat::GREEN."Opped: ".TextFormat::WHITE
				 . ($target->isOp() ? "YES" : "NO").TextFormat::RESET;
		$txt[] = TextFormat::GREEN."Dislay Name: ".TextFormat::WHITE
				 . $target->getDisplayName().TextFormat::RESET;
		$txt[] = TextFormat::GREEN."Flying: ".TextFormat::WHITE
				 . ($target->isOnGround() ? "NO" : "YES").TextFormat::RESET;
		return $this->paginateText($sender,$pageNumber,$txt);

	}
}
