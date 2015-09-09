<?php
//= cmd:prefix,Player_Management
//: prepend prefix to chat lines
//> usage: **prefix** _[-n]_ _<prefix text>_
//:
//: This allows you to prepend a prefix to chat lines.
//: To stop enter **/prefix** by itself (or **prefix** at the console).
//: Usage examples:
//:
//: - Send multiple **/as player** commands in a row.
//: - Start a private chat **/tell player** with another player.
//: - You prefer commands over chat: **/prefix -n /**
//:
//: When prefix is enabled and you one to send just _one_ command without
//: prefix, prepend your text with **<**.

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\Player;

use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\server\RemoteServerCommandEvent;
use pocketmine\event\server\ServerCommandEvent;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\PermUtils;

class CmdPrefixMgr extends BasicCli implements CommandExecutor,Listener {
	static $delay = 5;
	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.prefix", "Prefix command", "true");

		$this->enableCmd("prefix",
							  ["description" => mc::_("Execute commands with prefix inserted"),
								"usage" => mc::_("/prefix [-n] <text>"),
								"permission" => "gb.cmd.prefix"]);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "prefix") return false;
		if (count($args) == 0 || (count($args) == 1 && $args[0] == "-n")) {
			$this->unsetState($sender);
			$sender->sendMessage(mc::_("prefix turned off"));
			return true;
		}
		$sep = " ";
		if ($args[0] == "-n") {
			$sep = "";
			array_shift($args);
		}
		$this->setState($sender,$n = implode(" ",$args).$sep);
		$sender->sendMessage(mc::_("Prefix set to \"%1%\"",$n));
		return true;
	}
	private function processCmd($msg,$sender) {
		$prefix = $this->getState($sender,"");
		if ($prefix == "") return false;
		if ($msg{0} == "<") return false; // Just this command we do it without prefix!
		if ($sender instanceof Player) {
			if (preg_match('/^\s*\/prefix\s*/',$msg)) return false;
		} else {
			if (preg_match('/^\s*prefix\s*/',$msg)) return false;
		}
		if (!($sender instanceof Player)) $sender->sendMessage(">> $prefix$msg");
		return $prefix.$msg;
	}
	/**
	 * @priority LOW
	 */
	public function onPlayerCmd(PlayerCommandPreprocessEvent $ev) {
		if ($ev->isCancelled()) return;
		$res = $this->processCmd($ev->getMessage(),$ev->getPlayer());
		if ($res === false) return;
		$ev->setMessage($res);
	}
	/**
	 * @priority LOW
	 */
	public function onRconCmd(RemoteServerCommandEvent $ev) {
		$res = $this->processCmd($ev->getCommand(),$ev->getSender());
		if ($res === false) return;
		$ev->setCommand($res);
	}
	/**
	 * @priority LOW
	 */
	public function onConsoleCmd(ServerCommandEvent $ev) {
		$res = $this->processCmd($ev->getCommand(),$ev->getSender());
		if ($res === false) return;
		$ev->setCommand($res);
	}
}
