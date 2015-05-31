<?php
/**
 ** OVERVIEW:Server Management
 **
 ** COMMANDS
 **
 ** * crash : manage crash dumps
 **   usage: **crash** _[ls|clean|show]_
 **
 **   Will show the number of `crash` files in the server.
 **   The following optional sub-commands are available:
 **   - **crash** **ls**
 **     - List crash files
 **   - **crash** **clean**
 **     - Delete all crash files
 **   - **show** ##
 **     - Shows the crash file ##
 **
 **/

namespace aliuly\grabbag;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use aliuly\common\BasicCli;
use aliuly\common\mc;

class CmdAfterAt extends BasicCli implements CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("crash",
							  ["description" => mc::_("manage crash files"),
								"usage" => mc::_("/crash [ls|clean|show]"),
								"permission" => "gb.cmd.after"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "crash") return;
		if (count($args) == 0) $args = [ "count" ];
		$scmd = strtolower(array_shift($args));

		switch($scmd) {
			case "count":
			case "clean":
			case "show":
			case "ls":


		return false;
	}
}
