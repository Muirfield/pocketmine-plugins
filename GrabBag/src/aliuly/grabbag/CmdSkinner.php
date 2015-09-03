<?php
//= cmd:skin,Player_Management
//: manage player's skins
//> usage: **skin** _[player]_ _[save|load|ls]_ _[name]_
//:
//: Manipulate player's skins on the server.
//: Sub-commands:
//> - **skin** **ls**
//:     - List all available skins on the server.  Default command.
//> - **skin** _[player]_ **save** _<name>_
//:     - Saves _player_'s skin to _name_.
//> - **skin** _[player]_ **load** _<name>_
//:     - Loads _player_'s skin from _name_.

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;

class CmdSkinner extends BasicCli implements CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("skin",
							  ["description" => mc::_("Manage skins on the server"),
								"usage" => mc::_("/skin [player] [save|load|ls] [name]"),
								"permission" => "gb.cmd.skin"]);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if ($cmd->getName() != "skin") return false;
		$pageNumber = $this->getPageNumber($args);
		if (isset($args[0])) {
			$human = $this->owner->getServer()->getPlayer($args[0]);
			if ($human !== null) {
				array_shift($args);
			} else {
				$human = $sender;
			}
		}
		if (count($args) == 0) $args = [ "ls" ];
		switch (strtolower(array_shift($args))) {
			case "ls":
				$skins = glob($this->owner->getDataFolder()."*.skin");
				if (count($skins) == 0) {
					$sender->sendMessage(mc::_("No skins found"));
					return true;
				}
				$txt = [ mc::n(mc::_("Found one skin"),
									mc::_("Found %1% skins",count($skins)),
									count($skins)) ];
				$cols = 8;
				$i = 0;
				foreach ($skins as $n) {
					$n = basename($n,".skin");
					if (($i++ % $cols) == 0) {
						$txt[] = $n;
					} else {
						$txt[count($txt)-1] .= ", ".$n;
					}
				}
				return $this->paginateText($sender,$pageNumber,$txt);
			case "save":
				if (count($args) != 1) return false;
				if (!MPMU::inGame($human)) return true;
				if ($human !== $sender && !MPMU::access($sender,"gb.cmd.skin.other")) return true;
				$fn = preg_replace('/\.skin$/','',basename($args[0])).".skin";
				$bin = zlib_encode($human->getSkinData(),ZLIB_ENCODING_DEFLATE,9);
				file_put_contents($this->owner->getDataFolder().$fn,$bin);
				$sender->sendMessage(mc::_("Wrote %1% bytes to %2%",strlen($bin),$fn));
				return true;
			case "load":
				$slim = false;
				if (isset($args[0]) && $args[0] == "--slim") {
					$slim = true;
					array_shift($args);
				}
				if ($human !== $sender && !MPMU::access($sender,"gb.cmd.skin.other")) return true;
				if (count($args) != 1) return false;
				if (!MPMU::inGame($human)) return true;
				$fn = preg_replace('/\.skin$/','',basename($args[0])).".skin";
				$bin = file_get_contents($this->owner->getDataFolder().$fn);
				if ($bin === false) {
					$sender->sendMessage(mc::_("Unable to read %1%",$fn));
					return true;
				}
				$human->setSkin(zlib_decode($bin),$slim);
				$sender->sendMessage(mc::_("Updated skin for %1%",
													$human->getName()));
				return true;
		}
		return false;
	}
}
