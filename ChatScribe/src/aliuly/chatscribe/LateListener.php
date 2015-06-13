<?php
namespace aliuly\chatscribe;
use aliuly\chatscribe\Main as ChatScribePlugin;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\server\RemoteServerCommandEvent;
use pocketmine\event\server\ServerCommandEvent;

class LateListener implements Listener {
	private $owner;
	public function __construct(ChatScribePlugin $owner) {
		$this->owner = $owner;
	}
	/**
	 * @priority MONITOR
	 */
	public function onPlayerCmd(PlayerCommandPreprocessEvent $ev) {
		if ($ev->isCancelled()) return;
		$this->owner->logMsg($ev->getPlayer(),$ev->getMessage());
	}
	/**
	 * @priority MONITOR
	 */
	public function onRconCmd(RemoteServerCommandEvent $ev) {
		$this->owner->logMsg($ev->getSender(),$ev->getCommand());
	}
	/**
	 * @priority MONITOR
	 */
	public function onConsoleCmd(ServerCommandEvent $ev) {
		$this->owner->logMsg($ev->getSender(),$ev->getCommand());
	}
}
