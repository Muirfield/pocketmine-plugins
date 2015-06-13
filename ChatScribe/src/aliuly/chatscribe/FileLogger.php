<?php
namespace aliuly\chatscribe;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use LogLevel;

use aliuly\chatscribe\common\mc;

class FileLogger {
	private $owner;
	private $file;
	public function __construct(PluginBase $owner,$target) {
		$this->owner = $owner;
		$fp = @fopen($target, "a");
		if ($fp === false) {
			$owner->getServer()->getLogger()->error(mc::_("Error writing to %1%",$target));
			throw new \RuntimeException("$target: unable to open");
			return;
		}
		fclose($fp);
		$this->file = $target;
	}
	public function logMsg(CommandSender $pl,$msg) {
		$txt =
			  date(mc::_("Y-m-d H:i:s"),time())." ".
			  "[".$pl->getName()."]: ".
			  $msg.
			  "\n";
		file_put_contents($this->file,$txt,FILE_APPEND);
	}
}
