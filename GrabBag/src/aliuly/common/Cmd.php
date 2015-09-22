<?php
namespace aliuly\common;
use aliuly\common\MPMU;
use pocketmine\command\RemoteConsoleCommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Player;
use pocketmine\command\PluginCommand;

/**
 * Utility class to execute commands|chat's as player or console
 */
abstract class Cmd {
	/**
	 * Execute a command as a given player
	 *
	 * @param Player|CommandSender $sender - Entity to impersonate
	 * @param str[]|str $cmd - commands to execute
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
	 * Execute a command capturing output
	 *
	 * @param Server $server
	 * @param str $cmd - command to execute
	 * @return str
	 */
	static public function system($server, $cmd) {
		$rcon = new RemoteConsoleCommandSender;
		$server->distpatchCommand($rcon,$cm);
		return $rcon->getMessage();
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
				$ctx->getServer()->dispatchCommand($ctx,$cm);
				$ctx->setOp(false);
				return;
			}
			$ctx->getServer()->dispatchCommand($ctx,$cm);
			return;
		}
		if (($cm = MPMU::startsWith($cmdline,"+console:")) !== null) {
			$ctx->getServer()->dispatchCommand(new ConsoleCommandSender,$cm);
			return;
		}
		if (($cm = MPMU::startsWith($cmdline,"+rcon:")) !== null) {
			if ($ctx instanceof Player) {
				$rcon = new RemoteConsoleCommandSender;
				$ctx->getServer()->distpatchCommand($rcon,$cm);
				if (trim($rcon->getMessage()) != "") $ctx->sendMessage($rcon->getMessage());
			} else {
				$ctx->getServer()->dispatchCommand($ctx,$cm);
			}
			return;
		}
		$ctx->getServer()->dispatchCommand($ctx,$cmdline);
	}
	/**
	 * Register a command
	 *
	 * @param Plugin $plugin - plugin that "owns" the command
	 * @param CommandExecutor $executor - object that will be called onCommand
	 * @param str $cmd - Command name
	 * @param array $yaml - Additional settings for this command.
	 */
	static public function addCommand($plugin, $executor, $cmd, $yaml) {
		$newCmd = new PluginCommand($cmd,$plugin);
		if (isset($yaml["description"]))
			$newCmd->setDescription($yaml["description"]);
		if (isset($yaml["usage"]))
			$newCmd->setUsage($yaml["usage"]);
		if(isset($yaml["aliases"]) and is_array($yaml["aliases"])) {
			$aliasList = [];
			foreach($yaml["aliases"] as $alias) {
				if(strpos($alias,":")!== false) {
					$this->owner->getLogger()->info("Unable to load alias $alias");
					continue;
				}
				$aliasList[] = $alias;
			}
			$newCmd->setAliases($aliasList);
		}
		if(isset($yaml["permission"]))
			$newCmd->setPermission($yaml["permission"]);
		if(isset($yaml["permission-message"]))
			$newCmd->setPermissionMessage($yaml["permission-message"]);
		$newCmd->setExecutor($executor);
		$cmdMap = $plugin->getServer()->getCommandMap();
		$cmdMap->register($plugin->getDescription()->getName(),$newCmd);
	}
	/**
	 * Unregisters a command
	 * @param Server|Plugin $obj - Access path to server instance
	 * @param str $cmd - Command name to remove
	 */
	static public function rmCommand($srv, $cmd) {
		$cmdMap = $srv->getCommandMap();
		$oldCmd = $cmdMap->getCommand($cmd);
		if ($oldCmd === null) return false;
		$oldCmd->setLabel($cmd."_disabled");
		$oldCmd->unregister($cmdMap);
		return true;
	}
}
