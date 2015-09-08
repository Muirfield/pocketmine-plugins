<?php

/**
 * Override commands example
 *
 * @name cmdmap
 * @main aliuly\example\CmdReMapper
 * @version 1.0.0
 * @api 1.12.0
 * @description Change default command implementations
 * @author aliuly
 * @softdepend libcommon
 */


namespace aliuly\example{
	use pocketmine\plugin\PluginBase;
	use pocketmine\command\ConsoleCommandSender;
	use pocketmine\command\CommandExecutor;
	use pocketmine\command\CommandSender;
	use pocketmine\command\Command;


	use aliuly\common\MPMU;
	use aliuly\common\Cmd;


	class CmdReMapper extends PluginBase implements CommandExecutor{
		public function onEnable(){
			Cmd::rmCommand($this->getServer(),"list");
			Cmd::addCommand($this,$this,"list",[
					"description" => "Replaced List command",
					"usage" => "/list",
				]);
		}
		public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
			switch($cmd->getName()) {
				case "list":
					Cmd::exec($sender,["query list"],false);
					return true;
			}
			return false;
		}
	}
}
