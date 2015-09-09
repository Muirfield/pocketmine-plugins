<?php
//= cmd:servicemode,Server_Management
//: controls servicemode
//> usage: **servicemode** **[on|off** _[message]_ **]**
//:
//: If **on** it will activate service mode.  In service mode new
//: players can not join (unless they are ops).  Existing players
//: can remain but may be kicked manually by any ops.
namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\PluginCallbackTask;
use aliuly\grabbag\common\PermUtils;


class CmdSrvModeMgr extends BasicCli implements CommandExecutor,Listener {
	protected $mode;
	static $delay = 5;
	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.servicemode", "service mode command", "op");
		PermUtils::add($this->owner, "gb.servicemode.allow", "login when in service mode", "op");

		$this->enableCmd("servicemode",
							  ["description" => mc::_("Enter/Exit servicemode"),
								"usage" => mc::_("/servicemode [on|off [message]]"),
								"aliases" => ["srvmode","srmode"],
								"permission" => "gb.cmd.servicemode"]);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$this->mode = false;
	}
	public function getServiceMode() {
		return $this->mode;
	}
	public function setServiceMode($msg) {
		$this->mode = $msg;
	}
	public function unsetServiceMode() {
		$this->mode = false;
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "servicemode") return false;
		if (count($args) == 0) {
			if ($this->getServiceMode() !== false) {
				$sender->sendMessage(TextFormat::RED.mc::_("In Service Mode: %1%",$this->getServiceMode()));
			} else {
				$sender->sendMessage(TextFormat::GREEN.mc::_("In Normal operating mode"));
			}
			return true;
		}
		if (in_array(strtolower(array_shift($args)),["on","up","true",1])) {
			$msg = implode(" ",$args);
			if (!$msg) $msg = mc::_("Scheduled maintenance");
			$this->owner->getServer()->broadcastMessage(TextFormat::RED.mc::_("ATTENTION: Entering service mode"));
			$this->owner->getServer()->broadcastMessage(TextFormat::YELLOW." - ".$msg);
			$this->setServiceMode($msg);
		} else {
			$this->owner->getServer()->broadcastMessage(TextFormat::GREEN.mc::_("ATTENTION: Leaving service mode"));
			$this->unsetServiceMode();
		}
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
			$task =new PluginCallbackTask($this->owner,[$this,"announce"],[$pl->getName()]);
		} else {
			$task =new PluginCallbackTask($this->owner,[$this,"kickuser"],[$pl->getName()]);
		}
		$this->owner->getServer()->getScheduler()->scheduleDelayedTask($task,self::$delay);
	}
	public function announce($pn) {
		$player = $this->owner->getServer()->getPlayer($pn);
		if (!($player instanceof Player)) return;
		$player->sendMessage(TextFormat::RED.mc::_("NOTE: currently in service mode"));
		$player->sendMessage(TextFormat::YELLOW."- ".$this->mode);
	}
	public function kickuser($pn) {
		$player = $this->owner->getServer()->getPlayer($pn);
		if (!($player instanceof Player)) return;
		$this->owner->getServer()->broadcastMessage(TextFormat::RED.mc::_("%1% attempted to join",$pn));
		$player->kick($this->mode);
	}
}
