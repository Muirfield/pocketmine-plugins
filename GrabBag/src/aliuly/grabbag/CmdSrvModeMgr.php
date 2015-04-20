<?php
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;

use pocketmine\scheduler\CallbackTask;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class CmdSrvModeMgr extends BaseCommand implements Listener {
	protected $mode;
	static $delay = 5;
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("servicemode",
							  ["description" => "Enter/Exit servicemode",
								"usage" => "/servicemode [on|off [message]]",
								"aliases" => ["srvmode","srmode"],
								"permission" => "gb.cmd.servicemode"]);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$this->mode = false;
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "servicemode") return false;
		if (count($args) == 0) {
			if ($this->mode !== false) {
				$sender->sendMessage(TextFormat::RED."In Service Mode: $mode");
			} else {
				$sender->sendMessage(TextFormat::GREEN."In Normal operating mode");
			}
			return true;
		}
		if (in_array(strtolower(array_shift($args)),["on","up","true",1])) {
			$msg = implode(" ",$args);
			if (!$msg) $msg = "Scheduled maintenance";
			$this->owner->getServer()->broadcastMessage("ATTENTION: Entering service mode");
			$this->owner->getServer()->broadcastMessage(" - ".$msg);
		} else {
			$msg = false;
			$this->owner->getServer()->broadcastMessage("ATTENTION: Leaving service mode");
		}
		$this->mode = $msg;
		return true;
	}
	//
	// Event handlers...
	//
	public function onPlayerJoin(PlayerJoinEvent $e) {
		$pl = $e->getPlayer();
		if ($pl == null) return;
		if ($this->mode === false) return;
		if ($pl->hasPermission("gb.servicemode.allow")) {
			$task =new CallbackTask([$this,"announce"],[$pl->getName()]);
		} else {
			$task =new CallbackTask([$this,"kickuser"],[$pl->getName()]);
		}
		$this->owner->getServer()->getScheduler()->scheduleDelayedTask($task,self::$delay);
	}
	public function announce($pn) {
		$player = $this->owner->getServer()->getPlayer($pn);
		if (!($player instanceof Player)) return;
		$player->sendMessage("NOTE: currently in service mode");
		$player->sendMessage("- ".$this->mode);
	}
	public function kickuser($pn) {
		$player = $this->owner->getServer()->getPlayer($pn);
		if (!($player instanceof Player)) return;
		$this->owner->getServer()->broadcastMessage("$pn attempted to join");
		$player->kick($this->mode);
	}
}
