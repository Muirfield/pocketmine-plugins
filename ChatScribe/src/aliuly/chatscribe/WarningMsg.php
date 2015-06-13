<?php
namespace aliuly\chatscribe;
use aliuly\chatscribe\Main as ChatScribePlugin;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class WarningMsg implements Listener {
	const tick = 15;
	private $owner;
	protected $msg;
	public function __construct(ChatScribePlugin $owner,$msg) {
		$this->owner = $owner;
		$this->msg = is_array($msg) ? $msg : [$msg];
		$owner->getServer()->getPluginManager()->registerEvents($this,$owner);
	}
	/**
	 * @priority MONITOR
	 */
	public function onJoin(PlayerJoinEvent $ev) {
		foreach($this->msg as $ln) {
			$ev->getPlayer()->sendMessage($ln);
		}
	}
}
