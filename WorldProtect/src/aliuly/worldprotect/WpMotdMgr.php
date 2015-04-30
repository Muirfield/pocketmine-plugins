<?php
namespace aliuly\worldprotect;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\event\Listener;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\Player;


class WpMotdMgr extends BaseWp implements Listener, CommandExecutor {
	protected $ticks;

	public function __construct(Plugin $plugin,$cfg) {
		parent::__construct($plugin);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$this->ticks = $cfg["ticks"];
		$this->enableSCmd("motd",["usage" => "[text]",
										  "help" => "Edits world motd text",
										  "permission" => "wp.cmd.wpmotd"]);

		$this->enableCmd("motd",
							  ["description"=>"Shows world motd text",
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
			$sender->sendMessage("[WP] Must specify a world");
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
			$c->sendMessage("[WP] motd for $world removed");
			return true;
		}
		$this->owner->setCfg($world,"motd",implode(" ",$args));
		$c->sendMessage("[WP] motd for $world updated");
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
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$pl = $ev->getEntity();
		if (!($pl instanceof Player)) return;
		$level = $ev->getTarget()->getName();
		$this->showMotd($pl,$level);
	}
}
