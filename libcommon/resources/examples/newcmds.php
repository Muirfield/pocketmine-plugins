<?php

/**
 * Example for New commands
 *
 * @name NewCmds
 * @main aliuly\example\NewCmds
 * @version 1.0.0
 * @api 1.12.0
 * @description Simple command implementations
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

	class NewCmds extends PluginBase implements CommandExecutor{
		public function onEnable(){
			Cmd::addCommand($this,$this,"sp",[
					"description" => "Sends popup to player",
					"usage" => "/sp <player> [message]",
				]);
			Cmd::addCommand($this,$this,"st",[
					"description" => "Sends tip to player",
					"usage" => "/st <player> [message]",
				]);
		}
		public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
			switch($cmd->getName()) {
				case "sp":
				  if (count($args)<2) return false;
					if (($pl = $this->getServer()->getPlayer($args[0])) == null) {
						$sender->sendMessage("$args[0] not found");
						return true;
					}
					array_shift($args);
					MPMU::sendPopup($pl,implode(" ",$args));
					return true;
				case "st":
				  if (count($args)<2) return false;
					if (($pl = $this->getServer()->getPlayer($args[0])) == null) {
						$sender->sendMessage("$args[0] not found");
						return true;
					}
					array_shift($args);
					$pl->sendTip(implode(" ",$args));
					return true;
			}
			return false;

		}
	}
}
