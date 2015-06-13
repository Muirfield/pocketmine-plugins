<?php
namespace aliuly\chatscribe;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use LogLevel;
use aliuly\chatscribe\common\mc;

class ServerLogger {
	private $owner;
	private $level;
	public function __construct(PluginBase $owner,$target) {
		$this->owner = $owner;
		switch (strtolower($target)) {
			case "emergency":
				$this->level = LogLevel::EMERGENCY;
				break;
			case "alert":
				$this->level = LogLevel::ALERT;
				break;
			case "critical":
				$this->level = LogLevel::CRITICAL;
				break;
			case "error":
				$this->level = LogLevel::ERROR;
				break;
			case "warning":
				$this->level = LogLevel::WARNING;
				break;
			case "notice":
				$this->level = LogLevel::NOTICE;
				break;
			case "info":
				$this->level = LogLevel::INFO;
				break;
			case "debug":
				$this->level = LogLevel::DEBUG;
				break;
			default:
				$owner->getServer()->getLogger()->error(mc::_("Invalid log target %1%",$target));
				$owner->getServer()->getLogger()->error(mc::_("Using \"debug\""));
				$this->level = LogLevel::DEBUG;
		}
	}
	public function logMsg(CommandSender $pl,$msg) {
		$this->owner->getServer()->getLogger()->log($this->level,$pl->getName().": ".$msg);
	}
}
