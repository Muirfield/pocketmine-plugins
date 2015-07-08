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

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;

class CmdSelMgr extends BasicCli implements Listener {
	static $delay = 5;
	public function __construct($owner) {
		parent::__construct($owner);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	private function processCmd($msg,$sender) {
	}
	/**
	 * @priority HIGHEST
	 */
	public function onPlayerCmd(PlayerCommandPreprocessEvent $ev) {
	}
	/**
	 * @priority HIGHEST
	 */
	public function onRconCmd(RemoteServerCommandEvent $ev) {
		$res = $this->processCmd($ev->getCommand(),$ev->getSender());
		if ($res === false) return;
		$ev->setCommand($res);
	}
	/**
	 * @priority HIGHEST
	 */
	public function onConsoleCmd(ServerCommandEvent $ev) {
		$res = $this->processCmd($ev->getCommand(),$ev->getSender());
		if ($res === false) return;
		$ev->setCommand($res);
	}
}
