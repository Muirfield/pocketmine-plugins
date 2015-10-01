<?php
//= cmd:chat-on|chat-off,Trolling
//: Allow players to opt-out from chat
//> usage: **chat-on|chat-off** _[player|--list|--server]_
//:
//: Prevents players from sending/receiving chat messages.
//: The following options are recognized:
//: - --list : Lists the players that have chat on/off status
//: - --server : Globally toggles on/off chat.
//:
//= cmd:clearchat,Player_Management
//: Clears your chat window
//> usage: **clearchat**
//:
//= cmd:nick,Player_Management
//: Change your display name
//> usage: **nick** _<name>_

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;

use aliuly\common\BasicCli;
use aliuly\common\mc;
use aliuly\common\MPMU;
use aliuly\common\PermUtils;

class CmdChatMgr extends BasicCli implements Listener,CommandExecutor {
	protected $chat;

	public function __construct($owner) {
		parent::__construct($owner);
		$this->chat = true;

		PermUtils::add($this->owner, "gb.cmd.togglechat", "lets players opt out from chat", "true");
		PermUtils::add($this->owner, "gb.cmd.togglechat.others", "lets you toggle chat for others", "op");
		PermUtils::add($this->owner, "gb.cmd.togglechat.excempt", "chat-off players will always receive chats from these players", "op");
		PermUtils::add($this->owner, "gb.cmd.togglechat.global", "Can toggle chat for the server as a whole", "op");
		PermUtils::add($this->owner, "gb.cmd.clearchat", "Clear your chat window", "true");
		PermUtils::add($this->owner, "gb.cmd.nick", "Change display name", "true");

		$this->enableCmd("clearchat",
										["description" => mc::_("clears your chat window"),
										"usage" => mc::_("/clearchat"),
										"permission" => "gb.cmd.clearchat"]);
		$this->enableCmd("chat-on",
										["description" => mc::_("starts chat"),
										"usage" => mc::_("/chat-on [player|-l|-g]"),
										"permission" => "gb.cmd.togglechat"]);
		$this->enableCmd("chat-off",
										["description" => mc::_("stops chat"),
										"usage" => mc::_("/chat-off [player|-l|-g]"),
										"permission" => "gb.cmd.togglechat"]);
		$this->enableCmd("nick",
										["description" => mc::_("change displayed name"),
										"usage" => mc::_("/nick <new-name>"),
										"permission" => "gb.cmd.nick"]);

		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}

	public function setGlobalChat($mode) {
		$this->chat = $mode;
	}
	public function getGlobalChat() {
		return $this->chat;
	}
	public function setPlayerChat($player,$mode) {
		$this->setState($player,!$mode);
	}
	public function getPlayerChat($player) {
		return !$this->getState($to,false);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		switch ($cmd->getName()) {
			case "nick":
				if (!MPMU::inGame($sender)) return true;
				if (count($args) == 0) {
					$sender->sendMessage(mc::_("Current nick is: %1%",$sender->getDisplayName()));
					return true;
				}
				if (count($args) !== 1)  return false;
				$this->owner->getServer(mc::_("%1% is now known as %2%",$sender->getDisplayName(),$args[0]));
				$sender->setDisplayName($args[0]);
				return true;
			case "clearchat":
		  	if (!MPMU::inGame($sender)) return true;
				if (count($args) != 0) return false;
				for($i=0;$i<32;++$i) $sender->sendMessage(" ");
				return true;
			case "chat-on":
			case "chat-off":
		  	if (count($args) > 0) {
		 			switch ($n = strtolower(array_shift($args))) {
						case "--list":
						case "--ls":
						case "-l":
						  if (!MPMU::access($sender,"gb.cmd.togglechat.others")) return true;
							$pageNumber = $this->getPageNumber($args);
							$txt = [ "" ];
							$cols = 8;
							$i = 0;
							foreach ($this->owner->getServer()->getOnlinePlayers() as $p) {
								if (!$this->getState($p,false)) continue;
								$n = $p->getDisplayName();
								if (($i++ % $cols) == 0) {
									$txt[] = $n;
								} else {
									$txt[count($txt)-1] .= ", ".$n;
								}
							}
							if ($i == 0) {
								$sender->sendMessage(mc::_("No players with chat off"));
								if (!$this->chat) $sender->sendMessage(mc::_("Chat is GLOBALLY off"));
								return true;
							}
							if ($this->chat) {
								$txt[0] = mc::n(mc::_("One player with chat off"),
																mc::_("%1% players with chat off",$i),
																$i);
							} else {
								$txt[0] = mc::_("Chat is GLOBALLY off");
							}
							return $this->paginateText($sender,$pageNumber,$txt);
						case "--server":
						case "--global":
						case "-g":
							if (count($args)) return false;
							if (!MPMU::access($sender,"gb.cmd.togglechat.global")) return true;
							if ($cmd->getName() == "chat-off") {
								$this->setGlobalChat(false);
								$this->owner->getServer()->broadcastMessage(mc::_("Chat disabled globally from %1%", $sender->getName()));
							} else {
								$this->setGlobalChat(true);
								$this->owner->getServer()->broadcastMessage(mc::_("Chat enabled globally from %1%", $sender->getName()));
							}
							return true;
						default:
							if (count($args)) return false;
							if (!MPMU::access($sender,"gb.cmd.togglechat.others")) return true;
							if (($player = MPMU::getPlayer($sender,$n)) === null) return true;
							if ($cmd->getName() == "chat-off") {
								$this->setState($player, true);
								$player->sendMessage(mc::_("Chat disabled from %1%",$sender->getName()));
								$sender->sendMessage(mc::_("Chat disabled for %1%",$player->getDisplayName()));
							} else {
								$this->unsetState($player);
								$player->sendMessage(mc::_("Chat enabled from %1%",$sender->getName()));
								$sender->sendMessage(mc::_("Chat enabled for %1%",$player->getDisplayName()));
							}
							return true;
						}
						return false;
				}
				if (!MPMU::inGame($sender)) return true;
				if ($cmd->getName() == "chat-off") {
					$this->setState($sender,true);
					$sender->sendMessage(mc::_("Chat disabled"));
				} else {
					$this->unsetState($sender);
					$sender->sendMessage(mc::_("Chat enabled"));
				}
				return true;
		}
		return false;
	}

	public function onChat(PlayerChatEvent $ev) {
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
		if ($ev->isCancelled()) return;
		$p = $ev->getPlayer();
		if ($p->hasPermission("gb.cmd.togglechat.excempt")) return; // Can always chat!
		if (!$this->chat) {
			$p->sendMessage(mc::_("Chat has been globally disabled!"));
			$ev->setCancelled();
			return;
		}
		if ($this->getState($p,false)) {
			$p->sendMessage(mc::_("You have chat disabled!  Use /chat-on"));
			$ev->setCancelled();
			return;
		}
		$recvr = [];
		foreach ($ev->getRecipients() as $to) {
			if ($this->getState($to,false)) continue;
			$recvr[] = $to;
		}
		$ev->setRecipients($recvr);
	}
}
