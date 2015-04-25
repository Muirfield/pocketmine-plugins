<?php
/**
 ** OVERVIEW:Trolling
 **
 ** COMMANDS
 **
 ** * mute|unmute : mutes/unmutes a player so they can not use chat
 **   usage: **mute|unmute** _[player]_
 **
 **   Stops players from chatting.  If no player specified it will show
 **   the list of muted players.
 **
 **/

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;

class CmdMuteMgr extends BaseCommand implements Listener {
	protected $mutes;

	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("mute",
							  ["description" => "mute player",
								"usage" => "/mute [player]",
								"permission" => "gb.cmd.mute"]);
		$this->enableCmd("unmute",
							  ["description" => "unmute player",
								"usage" => "/unmute [player]",
								"permission" => "gb.cmd.mute"]);
		$this->mutes = [];
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if (count($args) == 0) {
			$sender->sendMessage("Mutes: ".count($this->mutes));
			if (count($this->mutes))
				$sender->sendMessage(implode(", ",$this->mutes));
			return true;
		}
		switch ($cmd->getName()) {
			case "mute":
				foreach ($args as $n) {
					$player = $this->owner->getServer()->getPlayer($n);
					if ($player) {
						$this->mutes[strtolower($player->getName())] = $player->getName();
						$player->sendMessage("You have been muted by ".
													$sender->getName());
						$sender->sendMessage("$n is muted.");
					} else {
						$sender->sendMessage("$n not found.");
					}
				}
				return true;
			case "unmute":
				foreach ($args as $n) {
					if (isset($this->mutes[strtolower($n)])) {
						unset($this->mutes[strtolower($n)]);
						$player = $this->owner->getServer()->getPlayer($n);
						if ($player) {
							$player->sendMessage("You have been unmuted by ".
														$sender->getName());
						}
						$sender->sendMessage("$n is un-muted");
					} else {
						$sender->sendMessage("$n not found or not muted");
					}
				}
				return true;
		}
		return false;
	}

	public function onChat(PlayerChatEvent $ev) {
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
		if ($ev->isCancelled()) return;
		$p = $ev->getPlayer();
		if (isset($this->mutes[strtolower($p->getName())])) {
			$p->sendMessage("You have been muted!");
			$ev->setCancelled();
		}
	}
}
