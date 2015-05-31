<?php
/**
 ** OVERVIEW:Basic Usage
 **
 ** COMMANDS
 **
 ** * /motd : Shows the world's *motd* text
 **   usage: /motd  _[world]_
 **   Shows the *motd* text of a _world_.  This can be used to show
 **   rules around a world.
 ** * motd : Modifies the world's *motd* text.
 **   usage: /wp _[world]_ **motd** _<text>_
 **   Let's you modify the world's *motd* text.  The command only
 **   supports a single line, however you can modify the *motd* text
 **   by editing the `wpcfg.yml` file that is stored in the `world`
 **   folder.  For example:
 **
 **       [CODE]
 **       motd:
 **       - line 1
 **       - line 2
 **       - line 3
 **       - line 4... etc
 **       [/CODE]
 **
 **
 ** CONFIG:motd
 **
 **/
namespace aliuly\worldprotect;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\event\Listener;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\Player;
use aliuly\common\PluginCallbackTask;
use aliuly\common\mc;

class WpMotdMgr extends BaseWp implements Listener, CommandExecutor {
	protected $ticks;

	static public function defaults() {
		return [
			"# ticks" => "line delay when showing multi-line motd texts.",
			"ticks" => 15,
		];
	}
	public function __construct(Plugin $plugin,$cfg) {
		parent::__construct($plugin);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$this->ticks = $cfg["ticks"];
		$this->enableSCmd("motd",["usage" => mc::_("[text]"),
										  "help" => mc::_("Edits world motd text"),
										  "permission" => "wp.cmd.wpmotd"]);

		$this->enableCmd("motd",
							  ["description"=>mc::_("Shows world motd text"),
								"usage" => "/motd [world]",
								"permission" => "wp.motd" ]);
	}


	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
		if ($cmd->getName() != "motd") return false;
		if ($sender instanceof Player) {
			$world = $sender->getLevel()->getName();
		} else {
			$level = $this->owner->getServer()->getDefaultLevel();
			if ($level) {
				$world = $level->getName();
			} else {
				$world = null;
			}
		}
		if (isset($args[0]) && $this->owner->getServer()->isLevelGenerated($args[0])) {
			$world = array_shift($args);
		}
		if ($world === null) {
			$sender->sendMessage(mc::_("[WP] Must specify a world"));
			return false;
		}
		if (count($args) != 0) return false;
		$this->showMotd($sender,$world);
		return true;
	}

	public function onSCommand(CommandSender $c,Command $cc,$scmd,$world,array $args) {
		if ($scmd != "motd") return false;
		if (count($args) == 0) {
			$this->owner->unsetCfg($world,"motd");
			$c->sendMessage(mc::_("[WP] motd for %1% removed",$world));
			return true;
		}
		$this->owner->setCfg($world,"motd",implode(" ",$args));
		$c->sendMessage(mc::_("[WP] motd for %1% updated",$world));
		return true;
	}

	private function showMotd($c,$world) {
		if (!$c->hasPermission("wp.motd")) return;

		$motd = $this->owner->getCfg($world, "motd", null);
		if ($motd === null) return true;
		if (is_array($motd)) {
			if ($c instanceof Player) {
				$ticks = $this->ticks;
				foreach ($motd as $ln) {
					$this->owner->getServer()->getScheduler()->scheduleDelayedTask(new PluginCallbackTask($this->owner,[$c,"sendMessage"],[$ln]),$ticks);
					$ticks += $this->ticks;
				}
			} else {
				foreach ($motd as $ln) {
					$c->sendMessage($ln);
				}
			}
		} else {
			$c->sendMessage($motd);
		}
	}

	public function onJoin(PlayerJoinEvent $ev) {
		$pl = $ev->getPlayer();
		$this->showMotd($pl,$pl->getLevel()->getName());
	}
	public function onLevelChange(EntityLevelChangeEvent $ev) {
		$pl = $ev->getEntity();
		if (!($pl instanceof Player)) return;
		$level = $ev->getTarget()->getName();
		$this->showMotd($pl,$level);
	}
}
