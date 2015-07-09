<?php
/**
 ** MODULE:CommandSelector
 **
 ** Adds "@" prefixes.
 **
 **/

namespace aliuly\grabbag;

use pocketmine\event\Listener;
use pocketmine\Player;

use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\server\RemoteServerCommandEvent;
use pocketmine\event\server\ServerCommandEvent;
use pocketmine\event\Timings;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;

class PlayerCommandPreprocessEvent_sub extends PlayerCommandPreprocessEvent{
}
class RemoteServerCommandEvent_sub extends RemoteServerCommandEvent{
}
class ServerCommandEvent_sub extends ServerCommandEvent{
}


class CmdSelMgr extends BasicCli implements Listener {
	protected $max;
	public function __construct($owner) {
		parent::__construct($owner);
		$this->max = 128;
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	/**
	 * @priority HIGHEST
	 */
	public function onPlayerCmd(PlayerCommandPreprocessEvent $ev) {
		if ($ev instanceof PlayerCommandPreprocessEvent_sub) return;
		$line = $event->getMessage();
		if(substr($line, 0, 1) !== "/") return;
		$res = $this->processCmd(substr($line,1),$ev->getPlayer());
		if ($res === false) return;
		$ev->setCancelled();
		foreach($res as $c) {
			$this->owner->getServer()->getPluginManager()->callEvent($ne = new PlayerCommandPreprocessEvent_sub($ev->getSender(), "/".$c));
			if($ne->isCancelled()) continue;
			if (substr($ne->getMessage(),0,1) !== "/") continue;
			$this->owner->getServer()->dispatchCommand($ne->getSender(), substr($ne->getMessage(),1));
		}
	}
	/**
	 * @priority HIGHEST
	 */
	public function onRconCmd(RemoteServerCommandEvent $ev) {
		if ($ev instanceof RemoteServerCommandEvent_sub) return;
		$res = $this->processCmd($ev->getCommand(),$ev->getSender());
		if ($res === false) return;
		$ev->setCancelled();
		foreach($res as $c) {
			$this->owner->getServer()->getPluginManager()->callEvent($ne = new RemoteServerCommandEvent_sub($ev->getSender(), $c));
			if($ne->isCancelled()) continue;
			$this->owner->getServer()->dispatchCommand($ne->getSender(), $ne->getCommand());
		}
	}
	/**
	 * @priority HIGHEST
	 */
	public function onConsoleCmd(ServerCommandEvent $ev) {
		if ($ev instanceof ServerCommandEvent_sub) return;
		$res = $this->processCmd($ev->getCommand(),$ev->getSender());
		if ($res === false) return;
		$ev->setCancelled();
		foreach($res as $c) {
			$this->owner->getServer()->getPluginManager()->callEvent($ne = new ServerCommandEvent_sub($ev->getSender(), $c));
			if($ne->isCancelled()) continue;
			$this->owner->getServer()->dispatchCommand($ne->getSender(), $ne->getCommand());
		}
	}

	protected function processCmd($cmd,CommandSender $sender) {
		$tokens = preg_split('/\s+/',$cmd);

		$res = [ $tokens ];
		$ret = false;

		foreach ($tokens as $argc=>$argv){
			if (!$argc) continue; // Trivial case...
			if (substr($argv,0,1) !== "@" ) continue;

			$selector = substr($argv, 1);
			$sargs = [];
			if(($i = strpos($selector, "[")) !== false){
				foreach (explode(",",substr($selector,$i+1,-1)) as $kv) {
					$sargs = explode("=",$kv,2);
				}
				$selector = substr($selector,$i-1);
			}
			$results = $this->dispatchSelector($sender , $selector,$sargs);
			if (!is_array($results)) continue;
			$ret = true;
			$new = [];

			foreach ($res as $i) {
				foreach ($results as $j) {
					$tmpLine = $i;
					$tmpLine[$argc] = $j;
					$new[] = $tmpLine;
					if (count($new) > $this->max) break;
				}
				if (count($new) > $this->max) break;
			}
			$res = $new;
		}
		if (!$ret) return false;
		$new = [];
		foreach ($res as $i) {
			$new[] = implode(" ",$i);
		}
		return $new;
	}
	protected function dispatchSelector(CommandSender $sender,$selector,array $args) {
		switch ($selector) {
			case "a":
				
			case "e":
			case "r":
			case "e":

		}
	}
}
