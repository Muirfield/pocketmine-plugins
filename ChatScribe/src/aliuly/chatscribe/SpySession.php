<?php
namespace aliuly\chatscribe;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;

use aliuly\chatscribe\common\mc;
use aliuly\chatscribe\common\MPMU;

class SpySession implements Listener {
	private $owner;
	private $sessions;
	public function __construct(PluginBase $owner) {
		$this->owner = $owner;
		$this->owner->getServer()->getPluginManager()->registerEvents($this,$this->owner);
		$this->sessions = [];
	}
	public function onQuit(PlayerQuitEvent $ev) {
		$pl = $ev->getPlayer();
		$n = strtolower($pl->getName());
		if(isset($this->sessions[$n])) unset($this->sessions[$n]);
		$this->stopSpying($pl);
	}
	public function stopSpying($pl) {
		$m = strtolower($pl->getName());
		foreach (array_keys($this->sessions) as $n) {
			if (isset($this->sessions[$n][$m])) unset($this->sessions[$n][$m]);
		}
	}
	public function logMsg($pl,$msg) {
		if (!($pl instanceof Player)) return;
		$n = strtolower($pl->getName());
		if (!isset($this->sessions[$n])) return;
		foreach ($this->sessions[$n] as $spy) {
			$spy->sendMessage(mc::_("SPY(%1%) %2%",$pl->getDisplayName(),$msg));
		}
	}
	public function onCmd($sender,$args) {
		if (!MPMU::inGame($sender)) return true;
		if (count($args) == 0) $args = ["ls"];
		$m = strtolower($sender->getName());
		switch ($scmd = strtolower(array_shift($args))) {
			case "stop":
				$sender->sendMessage(mc::_("Stopping tapping"));
				if (count($args) == 0) {
					$this->stopSpying($sender);
				} else {
					foreach ($args as $n) {
						$n =strtolower($n);
						if (isset($this->sessions[$n][$m])) unset($this->sessions[$n][$m]);
					}
				}
				return true;
			case "start":
				$sender->sendMessage(mc::_("Starting tapping"));
				if (count($args) == 0) return false;
				foreach ($args as $n) {
					$pl = $this->owner->getServer()->getPlayer($n);
					if ($pl == null) {
						$sender->sendMessage(mc::_("%1% cannot be found",$n));
						continue;
					}
					if ($pl->hasPermission("chatscribe.privacy")) {
						$sender->sendMessage(mc::_("%1% has privacy, cannot be spied",$n));
						continue;
					}
					$pl->sendMessage(mc::_("$n is now tapping you"));
					$n = strtolower($pl->getName());
					if (!isset($this->sessions[$n])) $this->sessions[$n] = [];
					$this->sessions[$n][$m] = $sender;
				}
				return true;
			case "ls":
			case "sess":
				if (count($args) != 0) return false;
				$str = "";
				foreach ($this->sessions as $i=>&$j) {
					if (!isset($j[$m])) continue;
					$str .= (strlen($str) == 0 ? "" : ", "). $i;
				}
				if ($str == "") {
					$sender->sendMessage(mc::_("No sessions found"));
				} else {
					$sender->sendMessage(mc::_("Spying on:"));
					$sender->sendMessage($str);
				}
				return true;
		}
		return false;
	}
}
