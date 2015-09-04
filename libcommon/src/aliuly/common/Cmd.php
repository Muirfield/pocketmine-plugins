<?php
namespace aliuly\common;
use aliuly\common\MPMU;
use pocketmine\command\RemoteConsoleCommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Player;
/**
 * Utility class to execute commands|chat's as player or console
 */
abstract class Cmd {
	/**
	 * Execute a command as a given player
	 *
	 * @param Player|CommandSender $sender - Entity to impersonate
	 * @param str[]|str $cmd - commands to exectue
	 * @param bool $show - show commands being executed
	 */
	static public function exec($sender,$cmd,$show=true) {
		if (!is_array($cmd)) $cmd=  [ $cmd ];
		foreach ($cmd as $c) {
			if($show)$sender->sendMessage("CMD> $c");
			$sender->getServer()->dispatchCommand($sender,$c);
		}
	}
	/**
	 * Chat a message as a given player
	 *
	 * @param Player|CommandSender $sender - Entity to impersonate
	 * @param str[]|str $msg - messages to send
	 */
	static public function chat($sender,$msgs) {
		if (!is_array($msgs)) $msgs=  [ $msg ];
		foreach ($msgs as $msg) {
			$sender->getServer()->getPluginManager()->callEvent($ev = new PlayerChatEvent($sender,$msg));
			if ($ev->isCancelled()) continue;
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
	 * Execute commands as console
	 *
	 * @param Server $server - pocketmine\Server instance
	 * @param str[]|str $cmd - commands to execute
	 * @param bool $show - show commands being executed
	 */
	static public function console($server,$cmd,$show=false) {
		if (!is_array($cmd)) $cmd=  [ $cmd ];
		foreach ($cmd as $c) {
			if ($show) $server->getLogger()->info("CMD> $cmd");
			$server->dispatchCommand(new ConsoleCommandSender(),$c);
		}
	}
	/**
	 * Handles command prefixes before dispatching commands.
	 *
	 * The following prefixes are recognized:
	 * - "+op:", temporarily gives the player Op (if the player is not Op yet)
	 * - "+console:", runs the command as if it was run from the console.
	 * - "+rcon:", runs the command as if it was run from a RemoteConsole,
	 *   capturing all output which is then send to the player.
	 *
	 * @param CommandSender $ctx - running context
	 * @param str $cmdline - command line to execute
	 */
	static public function opexec(CommandSender $ctx, $cmdline) {
		if (($cm = MPMU::startsWith($cmdline,"+op:")) !== null) {
			if (!$ctx->isOp()) {
				$ctx->setOp(true);
				$ctx->getServer()->distpatchCommand($ctx,$cm);
				$ctx->setOp(false);
				return;
			}
			$ctx->getServer()->distpatchCommand($ctx,$cm);
			return;
		}
		if (($cm = MPMU::startsWith($cmdline,"+console:")) !== null) {
			$ctx->getServer()->distpatchCommand(new ConsoleCommandSender,$cm);
			return;
		}
		if (($cm = MPMU::startsWith($cmdline,"+rcon:")) !== null) {
			if ($ctx instanceof Player) {
				$rcon = new RemoteConsoleCommandSender;
				$ctx->getServer()->distpatchCommand($rcon,$cm);
				if (trim($rcon->getMessage()) != "") $ctx->sendMessage($rcon->getMessage());
			} else {
				$ctx->getServer()->distpatchCommand($ctx,$cm);
			}
			return;
		}
		$ctx->getServer()->dispatchCommand($ctx,$cmdline);
	}

}
