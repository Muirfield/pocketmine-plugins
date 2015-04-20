<?php
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\Player;

use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\server\RemoteServerCommandEvent;
use pocketmine\event\server\ServerCommandEvent;

class CmdPrefixMgr extends BaseCommand implements Listener {
	protected $mode;
	static $delay = 5;
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("prefix",
							  ["description" => "Execute commands with prefix inserted",
								"usage" => "/prefix [-n] <text>",
								"permission" => "gb.cmd.prefix"]);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$this->mode = false;
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "prefix") return false;
		if (count($args) == 0 || (count($args) == 1 && $args[0] == "-n")) {
			$this->unsetState($sender);
			$sender->sendMessage("prefix turned off");
			return true;
		}
		$sep = " ";
		if ($args[0] == "-n") {
			$sep = "";
			array_shift($args);
		}
		$this->setState($sender,$n = implode(" ",$args).$sep);
		$sender->sendMessage("Prefix set to \"$n\"");
		return true;
	}
	private function processCmd($msg,$sender) {
		$prefix = $this->getState($sender,"");
		if ($prefix == "") return false;
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
