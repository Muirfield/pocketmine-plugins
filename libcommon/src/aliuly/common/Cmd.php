<?php
namespace aliuly\common;
use aliuly\common\MPMU;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\player\PlayerChatEvent;

/**
 * Utility class to execute commands|chat's as player or console
 */
abstract class Cmd {
	/**
	 * Execute a command as a given player
	 *
	 * @param Player|CommandSender $sender - Entity to impersonate
	 * @param str $cmd - command to exectue
	 */
	static public function exec($sender,$cmd) {
		$sender->getServer()->dispatchCommand($sender,$cmd);
	}
	/**
	 * Chat a message as a given player
	 *
	 * @param Player|CommandSender $sender - Entity to impersonate
	 * @param str $msg - message to send
	 */
	static public function chat($sender,$msg) {
		$sender->getServer()->getPluginManager()->callEvent($ev = new PlayerChatEvent($sender,$msg));
		if (!$ev->isCancelled()) {
			if (MPMU::apiVersion("1.12.0")) {
				$s = $sender->getServer();
				$s->broadcastMessage($s->getLanguage()->translateString(
					$ev->getFormat(),
					[$ev->getPlayer()->getDisplayName(), $ev->getMessage()]),
											$ev->getRecipients());
			} else {
				$sender->getServer()->broadcastMessage(sprintf(
					$ev->getFormat(),
					$ev->getPlayer()->getDisplayName(),
					$ev->getMessage()),$ev->getRecipients());
			}
		}
	}
	/**
	 * Execute a command as console
	 *
	 * @param Server $server - pocketmine\Server instance
	 * @param str $cmd - command to exectue
	 */
	static public function console($server,$cmd) {
		$server->dispatchCommand(new ConsoleCommandSender(),$cmd);
	}
}
