<?php
/**
 ** OVERVIEW:Server Management
 **
 ** COMMANDS
 **
 ** * pluginmgr : manage plugins
 **   usage: **pluginmgr** _<enable|disable|reload|info|commands|permissions|load>_ _plugin>
 **
 **   Manage plugins.
 **   The following sub-commands are available:
 **   - **pluginmgr** **enable** _&lt;plugin&gt;_
 **     - Enable a disabled plugin.
 **   - **pluginmgr** **disable** _&lt;plugin&gt;_
 **     - Disables an enabled plugin.
 **   - **pluginmgr** **reload** _&lt;plugin&gt;_
 **     - Disables and enables a plugin.
 **   - **pluginmgr** **info** _&lt;plugin&gt;_
 **     - Show plugin details
 **   - **pluginmgr** **commands** _&lt;plugin&gt;_
 **     - Show commands registered by plugin
 **   - **pluginmgr** **permissions** _&lt;plugin&gt;_
 **     - Show permissions registered by plugin
 **   - **pluginmgr** **load** _&lt;path&gt;_
 **     - Load a plugin from file path (presumably outside the **plugin** folder.)
 **
 **/

namespace aliuly\grabbag;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;

use pocketmine\plugin\Plugin;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;

class CmdPluginMgr extends BasicCli implements CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("pluginmgr",
							  ["description" => mc::_("manage plugins"),
								"usage" => mc::_("/pluginmgr <enable|disable|reload|info|commands|permissions|load> <plugin>"),
								"aliases" => ["pm"],
								"permission" => "gb.cmd.pluginmgr"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "pluginmgr") return false;
		$pageNumber = $this->getPageNumber($args);
		if (count($args) < 2) return false;

		$scmd = strtolower(array_shift($args));
		$pname = array_shift($args);

		$mgr = $this->owner->getServer()->getPluginManager();
		if ($scmd == "load" || $scmd == "ld") {
			if (!file_exists($pname)) {
				$sender->sendMessage(TextFormat::RED.mc::_("%1%: Not found",$pname));
				return true;
			}
			$plugin = $mgr->loadPlugin($pname);
			if ($plugin === null) {
				$sender->sendMessage(TextFormat::RED.mc::_("Unable to load plugin from %1%",$pname));
				return true;
			}
			$sender->sendMessage(TextFormat::BLUE.mc::_("Loaded plugin %1%", $plugin->getDescription()->getFullName()));
			$mgr->enablePlugin($plugin);
			return true;
		}

		$plugin = $mgr->getPlugin($pname);
		if ($plugin === null) {
			$sender->sendMessage(TextFormat::RED.mc::_("Plugin %1% not found",
																	 $pname));
			return true;
		}

		switch($scmd) {
			case "ena":
			case "start":
			case "enable":
				if ($plugin->isEnabled()) {
					$sender->sendMessage(TextFormat::RED.
												mc::_("%1% is already enabled",$pname));
					break;
				}
				$mgr->enablePlugin($plugin);
				$sender->sendMessage(TextFormat::GREEN.
											mc::_("Plugin %1% enabled",$pname));
				break;
			case "disable":
			case "dis":
			case "stop":
				if (!$plugin->isEnabled()) {
					$sender->sendMessage(TextFormat::RED.
												mc::_("%1% is already disabled",$pname));
					break;
				}
				$mgr->disablePlugin($plugin);
				$sender->sendMessage(TextFormat::GREEN.
											mc::_("Plugin %1% disabled",$pname));

				break;
			case "reload":
			case "restart":
			case "reenable":
			case "re":
				if (!$plugin->isEnabled()) {
					$sender->sendMessage(TextFormat::RED.
												mc::_("%1% is not enabled",$pname));
					break;
				}
				$mgr->disablePlugin($plugin);
				$mgr->enablePlugin($plugin);
				$sender->sendMessage(TextFormat::GREEN.
											mc::_("Plugin %1% reloaded",$pname));
				break;
			case "info":
				return $this->cmdInfo($sender,$plugin,$pageNumber);
			case "cmds":
			case "com":
			case "command":
			case "commands":
				return $this->cmdCmds($sender,$plugin,$pageNumber);
			case "perms":
			case "permission":
			case "permissions":
				return $this->cmdPerms($sender,$plugin,$pageNumber);
			default:
				$sender->sendMessage(mc::_("Unknown sub-command %1%",$scmd));
				return false;
		}
		return true;
	}
	private function cmdPerms(CommandSender $c,Plugin $p,$pageNumber) {
		$desc = $p->getDescription();
		$perms = $desc->getPermissions();
		if (count($perms) == 0) {
			$c->sendMessage(TextFormat::RED,mc::_("%1% has no configured permissions",
															  $p->getName()));
			return true;
		}
		$txt = [];
		$txt[] = TextFormat::AQUA.mc::_("Plugin: %1%",$desc->getFullName());
		foreach ($perms as $p) {
			$txt[] = TextFormat::GREEN.$p->getName().": ".
					 TextFormat::WHITE.$p->getDescription();
		}
		return $this->paginateText($c,$pageNumber,$txt);
	}
	private function cmdCmds(CommandSender $c,Plugin $p,$pageNumber) {
		$desc = $p->getDescription();
		$cmds = $desc->getCommands();
		if (count($cmds) == 0) {
			$c->sendMessage(TextFormat::RED,mc::_("%1% has no configured commands",
															  $p->getName()));
			return true;
		}
		$txt = [];
		$txt[] = TextFormat::AQUA.mc::_("Plugin: %1%",$desc->getFullName());
		foreach ($cmds as $i=>$j) {
			$d = isset($j["description"]) ? $j["description"] : "";
			$txt[] = TextFormat::GREEN.$i.": ".TextFormat::WHITE.$d;
		}
		return $this->paginateText($c,$pageNumber,$txt);
	}

	private function cmdInfo(CommandSender $c,Plugin $p,$pageNumber) {
		$txt = [];
		$desc = $p->getDescription();
		$txt[] = TextFormat::AQUA.mc::_("Plugin: %1%",$desc->getFullName());
		if ($desc->getDescription())
			$txt[] = TextFormat::GREEN.mc::_("Description: ").
					 TextFormat::WHITE.$desc->getDescription();
		if ($desc->getPrefix())
			$txt[] = TextFormat::GREEN.mc::_("Prefix: ").
					 $txt[] = TextFormat::GREEN.mc::_("Main Class: ").
					 TextFormat::WHITE.$desc->getMain();
		if ($desc->getWebsite())
			$txt[] = TextFormat::GREEN.mc::_("WebSite: ").
					 TextFormat::WHITE.$desc->getWebsite();
		if (count($desc->getCompatibleApis()))
			$txt[] = TextFormat::GREEN.mc::_("APIs: ").
					 TextFormat::WHITE.implode(", ",$desc->getCompatibleApis());
		if (count($desc->getAuthors()))
			$txt[] = TextFormat::GREEN.mc::_("Authors: ").
					 TextFormat::WHITE.implode(", ",$desc->getAuthors());
		if (count($desc->getDepend()))
			$txt[] = TextFormat::GREEN.mc::_("Dependancies: ").
					 TextFormat::WHITE.implode(", ",$desc->getDepend());
		if (count($desc->getSoftDepend()))
			$txt[] = TextFormat::GREEN.mc::_("Soft-Dependancies: ").
					 TextFormat::WHITE.implode(", ",$desc->getSoftDepend());
		if (count($desc->getLoadBefore()))
			$txt[] = TextFormat::GREEN.mc::_("Load Before: ").
					 TextFormat::WHITE.implode(", ",$desc->getLoadBefore());
		if (($cnt = count($desc->getCommands())) > 0)
			$txt[] = TextFormat::GREEN.mc::_("Commands: ").TextFormat::WHITE.$cnt;
		if (($cnt = count($desc->getPermissions())) > 0)
			$txt[] = TextFormat::GREEN.mc::_("Permissions: ").TextFormat::WHITE.$cnt;
		return $this->paginateText($c,$pageNumber,$txt);
	}
}
