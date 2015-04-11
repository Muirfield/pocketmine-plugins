<?php
namespace grabbag;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
//use pocketmine\scheduler\CallbackTask;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\RemoteServerCommandEvent;
use pocketmine\event\server\ServerCommandEvent;


class RepeatMgr implements Listener {
	public $owner;
	protected $last;
	public function __construct(Plugin $plugin) {
		$this->owner = $plugin;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$last = [];
	}
	public function processCmd($msg,$player,$sender) {
		if (preg_match('/^\s*!!/',$msg)) {
			$msg = trim(preg_replace('/^\s*!!\s*/','',$msg));
			// Match !
			if (!isset($this->last[$player])) {
				$sender->sendMessage("You do not have any recorded previous command");
				return false;
			}
			// Just the previous command...
			if ($msg == "") return $this->last[$player];
			if (is_numeric($msg)) {
				// We need to replace the last word with $msg....
				$words = preg_split('/\s+/',$this->last[$player]);
				if (count($words) == 1) {
					// Only a single world, we append the number...
					$newmsg = $this->last[$player]." ".$msg;
				} else {
					if (is_numeric($words[count($words)-1])) {
						// Exchange the last word (page count)
						$words[count($words)-1] = $msg;
						$newmsg = implode(" ",$words);
					} else {
						// Last word wasn't a number... append one
						$newmsg = $this->last[$player]." ".$msg;
					}
				}
			} else {
				$words = preg_split('/\s+/',$msg,2);
				if (count($words) > 1
					 && stristr($this->last[$player],$words[0]) !== false) {
					// Replace string
					$newmsg = str_ireplace($words[0],$words[1],$this->last[$player]);
				} else {
					// Add string...
					$newmsg = $this->last[$player].' '.$msg;
				}
			}
			$sender->sendMessage(">> $newmsg");
			$this->last[$player] = $newmsg;
			return $newmsg;
		}
		$this->last[$player] = $msg;
		return false;
	}

	public function onPlayerQuit(PlayerQuitEvent $ev) {
		if (isset($this->last[$ev->getPlayer()->getName()]))
			unset($this->last[$ev->getPlayer()->getName()]);
	}
	public function onPlayerCmd(PlayerCommandPreprocessEvent $ev) {
		$res = $this->processCmd($ev->getMessage(),$ev->getPlayer()->getName(),
										 $ev->getPlayer());
		if ($res === false) return;
		$ev->setMessage($res);
	}
	public function onRconCmd(RemoteServerCommandEvent $ev) {
		$res = $this->processCmd($ev->getCommand(),"[RCON]",
										 $ev->getSender());
		if ($res === false) return;
		$ev->setCommand($res);
	}
	public function onConsoleCmd(ServerCommandEvent $ev) {
		$res = $this->processCmd($ev->getCommand(),"[CONSOLE]",
										 $ev->getSender());
		if ($res === false) return;
		$ev->setCommand($res);
	}
}
