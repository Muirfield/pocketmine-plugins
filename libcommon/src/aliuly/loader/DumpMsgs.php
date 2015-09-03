<?php
//= cmd:dumpmsg
//: Dump a plugin's messages.ini
//> usage: /libcommon **dumpmsg** _<plugin>_
//:
//: This command is available when **DEBUG** is enabled.
namespace aliuly\loader;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use aliuly\common\mc;
use aliuly\common\BasicCli;

class DumpMsgs extends BasicCli {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableSCmd("dumpmsg",["usage" => "<plugin>",
										"help" => mc::_("Dump a plugins messages.ini")]);
	}
	public function onSCommand(CommandSender $c,Command $cc,$scmd,$data,array $args) {
		if (count($args) != 1) return false;
    $pname = $args[0];
    $mgr = $this->owner->getServer()->getPluginManager();
    $plugin = $mgr->getPlugin($pname);
    if ($plugin === null) {
      $c->sendMessage(mc::_("%1%: plugin not found",$pname));
      return true;
    }
    $getini = [$plugin,"getMessagesIni"];
    if (!is_callable($getini)) {
      $c->sendMessage(mc::_("%1%: does not support dumping messages.ini",$pname));
      return true;
    }
    if (!is_dir($plugin->getDataFolder())) mkdir($plugin->getDataFolder());
		if (file_put_contents($plugin->getDataFolder()."messages.ini",$getini())) {
    	$c->sendMessage(mc::_("%1%: messages.ini created",$pname));
    } else {
      $c->sendMessage(mc::_("%1%: error dumping messages.ini", $pname));
    }
    return true;
  }
}
