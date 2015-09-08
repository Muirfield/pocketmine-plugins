<?php
//= cmd:bancmd|unbancmd,Sub_Commands
//: Prevents commands to be used in worlds
//> usage: /wp _[world]_ **bancmd|unbancmd** _[command]_
//:
//: If no commands are given it will show a list of banned
//: commands.   Otherwise the _command_ will be added/removed
//: from the ban list
//:
//= features
//: * Ban commands on a per world basis
namespace aliuly\worldprotect;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\Player;
use aliuly\worldprotect\common\mc;

class BanCmd extends BaseWp implements Listener {
	public function __construct(Plugin $plugin) {
		parent::__construct($plugin);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$this->enableSCmd("bancmd",["usage" => mc::_("[command]"),
													"help" => mc::_("Bans the given command"),
													"permission" => "wp.cmd.bancmd"]);
		$this->enableSCmd("unbancmd",["usage" => mc::_("[command]"),
												 "help" => mc::_("Unbans command"),
												 "permission" => "wp.cmd.bancmd"]);
	}

	public function onSCommand(CommandSender $c,Command $cc,$scmd,$world,array $args) {
		if ($scmd != "bancmd" && $scmd != "unbancmd") return false;
		if (count($args) == 0) {
			$cmds = $this->owner->getCfg($world, "bancmds", []);
			if (count($cmds) == 0) {
				$c->sendMessage(mc::_("[WP] No banned commands in %1%",$world));
			} else {
				$c->sendMessage(mc::_("[WP] Commands(%1%): %2%",count($cmds), implode(", ",$cmds)));
			}
			return true;
		}
		$cc = 0;
		$cmds = $this->owner->getCfg($world, "bancmds", []);
		if ($scmd == "unbancmd") {
			foreach ($args as $i) {
				if ($i{0} !== "/") $i = "/".$i;
				$i = strtolower($i);
				if (isset($cmds[$i])) {
					unset($cmds[$i]);
					++$cc;
				}
			}
		} elseif ($scmd == "bancmd") {
			foreach ($args as $i) {
				if ($i{0} !== "/") $i = "/".$i;
				$i = strtolower($i);
				if (isset($cmds[$i])) continue;
				$cmds[$i] = $i;
				++$cc;
			}
		} else {
			return false;
		}
		if (!$cc) {
			$c->sendMessage(mc::_("No commands updated"));
			return true;
		}
		if (count($cmds)) {
			$this->owner->setCfg($world,"bancmds",$cmds);
		} else {
			$this->owner->unsetCfg($world,"bancmds");
		}
		$c->sendMessage(mc::_("Commands changed: %1%",$cc));
		return true;
	}
	/**
	 * @priority LOWEST
	 */
	public function onCmd(PlayerCommandPreprocessEvent $ev) {
		echo __METHOD__.",".__LINE__."\n";//##DEBUG

		if ($ev->isCancelled()) return;
		$pl = $ev->getPlayer();
		$world = $pl->getLevel()->getName();
		if (!isset($this->wcfg[$world])) return;
		$cmdline = trim($ev->getMessage());
		if ($cmdline == "") return;
		$cmdline = preg_split('/\s+/',$cmdline);
		$cmd = strtolower($cmdline[0]);
		if (!isset($this->wcfg[$world][$cmd])) return;
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$pl->sendMessage(mc::_("That command is banned here!"));
		$ev->setCancelled();
	}
}
