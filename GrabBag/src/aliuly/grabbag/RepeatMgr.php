<?php
namespace aliuly\grabbag;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\Player;

use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\server\RemoteServerCommandEvent;
use pocketmine\event\server\ServerCommandEvent;


class RepeatMgr implements Listener {
	public $owner;

	public function __construct(Plugin $plugin) {
		$this->owner = $plugin;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	public function processCmd($msg,$sender) {
		if (preg_match('/^\s*!!/',$msg)) {
			$msg = trim(preg_replace('/^\s*!!\s*/','',$msg));
			// Match !
			$last = $this->owner->getState("RepeatMgr",$sender,false);

			if ($last === false) {
				$sender->sendMessage("You do not have any recorded previous command");
				return false;
			}
			// Just the previous command...
			if ($msg == "") return $last;
			if (is_numeric($msg)) {
				// We need to replace the last word with $msg....
				$words = preg_split('/\s+/',$last);
				if (count($words) == 1) {
					// Only a single world, we append the number...
					$newmsg = $last." ".$msg;
				} else {
					if (is_numeric($words[count($words)-1])) {
						// Exchange the last word (page count)
						$words[count($words)-1] = $msg;
						$newmsg = implode(" ",$words);
					} else {
						// Last word wasn't a number... append one
						$newmsg = $last." ".$msg;
					}
				}
			} elseif ($msg == "/" && substr($last,0,1) != "/") {
				// Forgotten "/"
				$newmsg = "/".$last;
			} elseif (substr($msg,0,1) == "^") {
				// Do we need space?
				if (preg_match('/^\s+/',$msg)) {
					$newmsg = trim(substr($msg,1))." ".$last;
				} else {
					$newmsg = trim(substr($msg,1)).$last;
				}
			} else {
				$words = preg_split('/\s+/',$msg,2);
				if (count($words) > 1
					 && stristr($last,$words[0]) !== false) {
					// Replace string
					$newmsg = str_ireplace($words[0],$words[1],$last);
				} else {
					// Add string...
					$newmsg = $last.' '.$msg;
				}
			}
			if (!($sender instanceof Player)) $sender->sendMessage(">> $newmsg");

			$last = $this->owner->setState("RepeatMgr",$sender,$newmsg);
			return $newmsg;
		}
		$last = $this->owner->setState("RepeatMgr",$sender,$msg);
		return false;
	}

	/**
	 * @priority LOWEST
	 */
	public function onPlayerCmd(PlayerCommandPreprocessEvent $ev) {
		if ($ev->isCancelled()) return;
		if (!$ev->getPlayer()->hasPermission("gb.module.repeater")) return;
		$res = $this->processCmd($ev->getMessage(),$ev->getPlayer());
		if ($res === false) return;
		$ev->setMessage($res);
	}
	/**
	 * @priority LOWEST
	 */
	public function onRconCmd(RemoteServerCommandEvent $ev) {
		$res = $this->processCmd($ev->getCommand(),$ev->getSender());
		if ($res === false) return;
		$ev->setCommand($res);
	}
	/**
	 * @priority LOWEST
	 */
	public function onConsoleCmd(ServerCommandEvent $ev) {
		$res = $this->processCmd($ev->getCommand(),$ev->getSender());
		if ($res === false) return;
		$ev->setCommand($res);
	}
}
