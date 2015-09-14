<?php
//= cmd:mute|unmute,Trolling
//: mutes/unmutes a player so they can not use chat
//> usage: **mute|unmute** _[player]_
//:
//: Stops players from chatting.  If no player specified it will show
//: the list of muted players.

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;

use aliuly\common\BasicCli;
use aliuly\common\mc;
use aliuly\common\PermUtils;


class CmdMuteMgr extends BasicCli implements Listener,CommandExecutor {
	protected $mutes;

	public function __construct($owner) {
		parent::__construct($owner);

		PermUtils::add($this->owner, "gb.cmd.mute", "mute/unmute players", "op");

		$this->enableCmd("mute",
							  ["description" => mc::_("mute player"),
								"usage" => mc::_("/mute [player]"),
								"permission" => "gb.cmd.mute"]);
		$this->enableCmd("unmute",
							  ["description" => mc::_("unmute player"),
								"usage" => mc::_("/unmute [player]"),
								"permission" => "gb.cmd.mute"]);
		$this->mutes = [];
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if (count($args) == 0) {
			$sender->sendMessage(mc::_("Mutes: %1%",count($this->mutes)));
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
						$player->sendMessage(mc::_("You have been muted by %1%",
															$sender->getName()));
						$sender->sendMessage(mc::_("%1% is muted.",$n));
					} else {
						$sender->sendMessage(mc::_("%1% not found.",$n));
					}
				}
				return true;
			case "unmute":
				foreach ($args as $n) {
					if (isset($this->mutes[strtolower($n)])) {
						unset($this->mutes[strtolower($n)]);
						$player = $this->owner->getServer()->getPlayer($n);
						if ($player) {
							$player->sendMessage(mc::_("You have been unmuted by %1%",
																$sender->getName()));
						}
						$sender->sendMessage(mc::_("%1% is un-muted",$n));
					} else {
						$sender->sendMessage(mc::_("%1% not found or not muted",$n));
					}
				}
				return true;
		}
		return false;
	}
	public function getMutes() {
		return array_keys($this->mutes);
	}
	public function setMute($player,$mode) {
		$n = strtolower($player->getName());
		if ($mode) {
			if (isset($this->mutes[$n])) return;
			$this->mutes[$n] = $player->getName();
		} else  {
			if (!isset($this->mutes[$n])) return;
			unset($this->mutes[$n]);
		}
	}
	public function getMute($player) {
		return isset($this->mutes[strtolower($player->getName())]);
	}

	public function onChat(PlayerChatEvent $ev) {
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
		if ($ev->isCancelled()) return;
		$p = $ev->getPlayer();
		if (isset($this->mutes[strtolower($p->getName())])) {
			$p->sendMessage(mc::_("You have been muted!"));
			$ev->setCancelled();
		}
	}
}
