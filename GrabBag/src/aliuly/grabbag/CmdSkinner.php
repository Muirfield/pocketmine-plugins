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
//> - **skin** _[player]_ **load** _[--slim]_ _<name>_
//:     - Loads _player_'s skin from _name_.
//> - **skin** _[player]_ **slim**
//:     - Make player's skin slim
//> - **skin** _[player]_ **thick**
//:     - Make player's skin non-slim
//> - **skin** **formats**
//:     - Show supported formats


namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use aliuly\common\BasicCli;
use aliuly\common\mc;
use aliuly\common\MPMU;
use aliuly\common\PermUtils;
use aliuly\common\SkinUtils;

class CmdSkinner extends BasicCli implements CommandExecutor {
	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.skin", "Manage skins", "op");
		PermUtils::add($this->owner, "gb.cmd.skin.other", "Manage other's skins", "op");

		$this->enableCmd("skin",
							  ["description" => mc::_("Manage skins on the server"),
								"usage" => mc::_("/skin [player] [save|load|ls|slim|thick|formats] [name]"),
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
			  $skins = [];
				foreach (glob($this->owner->getDataFolder()."*.*") as $f) {
					if (SkinUtils::isSkinFile($f)) $skins[] = $f;
				}

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
			case "formats":
			case "fmt":
			  if (count($args) != 0) return false;
				$sender->sendMessage(mc::_("Supported formats: %1%", implode(", ", SkinUtils::formats())));
				return true;
			case "save":
				if (count($args) != 1) return false;
				if (!MPMU::inGame($human)) return true;
				if ($human !== $sender && !MPMU::access($sender,"gb.cmd.skin.other")) return true;
				if (SkinUtils::isPngExt($args[0])) {
					$fmts = SkinUtils::formats();
					if (!isset($fmts[SkinUtils::PNG_FMT])) {
						$sender->sendMessage(mc::_("PNG format is not supported"));
						return true;
					}
					$fn =  $this->owner->getDataFolder().preg_replace('/\.[pP][nN][gG]$/','',basename($args[0])).".png";
				} else {
					$fn =  $this->owner->getDataFolder().preg_replace('/\.skin$/','',basename($args[0])).".skin";
				}
				$cnt = SkinUtils::saveSkin($human,$fn);
				$sender->sendMessage(mc::_("Wrote %1% bytes to %2%",$cnt,$fn));
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
				if (SkinUtils::isPngExt($args[0])) {
					$fmts = SkinUtils::formats();
					if (!isset($fmts[SkinUtils::PNG_FMT])) {
						$sender->sendMessage(mc::_("PNG format is not supported"));
						return true;
					}
					$fn =  $this->owner->getDataFolder().preg_replace('/\.[pP][nN][gG]$/','',basename($args[0])).".png";
				} else {
					$fn =  $this->owner->getDataFolder().preg_replace('/\.skin$/','',basename($args[0])).".skin";
				}

				if (SkinUtils::loadSkin($human,$slim,$fn)) {
					$sender->sendMessage(mc::_("Updated skin for %1%",$human->getName()));
				} else {
					$sender->sendMessage(mc::_("Unable to read %1%",$fn));
				}
				return true;
			case "slim":
				if ($human !== $sender && !MPMU::access($sender,"gb.cmd.skin.other")) return true;
				if (count($args) != 0) return false;
				if (!MPMU::inGame($human)) return true;
				SkinUtils::setSlim($human, true);
				$sender->sendMessage(mc::_("%1% is now slim", $human->getName()));
				return true;
			case "thick":
				if ($human !== $sender && !MPMU::access($sender,"gb.cmd.skin.other")) return true;
				if (count($args) != 0) return false;
				if (!MPMU::inGame($human)) return true;
				SkinUtils::setSlim($human, false);
				$sender->sendMessage(mc::_("%1% is now thick", $human->getName()));
				return true;
		}
		return false;
	}
}
