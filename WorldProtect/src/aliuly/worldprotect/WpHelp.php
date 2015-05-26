<?php
namespace aliuly\worldprotect;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use aliuly\common\mc;

class WpHelp extends BaseWp {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableSCmd("help",["aliases"=>["?"]]);
	}
	public function onSCommand(CommandSender $c,Command $cc,$scmd,$world,array $args) {
		$cm = $this->owner->getScmdMap();
		$pageNumber = $this->getPageNumber($args);

		if (count($args)) {
			if ($args[0] == "usage") {
				if (!isset($cm["usage"][$scmd])) return false;
				$c->sendMessage(TextFormat::RED."Usage: /".$cc->getName().
									 " [world] $scmd ".$cm["usage"][$scmd]);
				return true;
			}

			$txt = [ "Help for ".$cc->getName() ];

			foreach ($args as $i) {
				if (isset($cm["alias"][$i])) $i=$cm["alias"][$i];
				if (!isset($cm["help"][$i]) && !isset($cm["usage"][$i])) {
					$txt[] = TextFormat::RED.mc::_("No help for %1%",$i);
					continue;
				}
				$txt[] = TextFormat::YELLOW.mc::_("Help: ").TextFormat::WHITE."/wp $i";
				if (isset($cm["help"][$i]))
					$txt[] = TextFormat::YELLOW.mc::_("Description: ").
							 TextFormat::WHITE.$cm["help"][$i];
				if (isset($cm["usage"][$i]))
					$txt[] = TextFormat::YELLOW.mc::_("Usage: ").
							 TextFormat::WHITE." /".$cc->getName().
							 " [world] $i ".$cm["usage"][$i];
			}
			return $this->paginateText($c,$pageNumber,$txt);
		}
		ksort($cm["help"]);
		$txt = [ mc::_("Available sub-commands for %1%",$cc->getName()) ];
		foreach ($cm["help"] as $cn => $desc) {
			$ln = TextFormat::GREEN.$cn;
			foreach ($cm["alias"] as $i => $j) {
				if ($j == $cn) $ln .= "|$i";
			}
			$ln .= ": ".TextFormat::WHITE."$desc";
			$txt[] = $ln;
		}
		return $this->paginateText($c,$pageNumber,$txt);
	}
}
