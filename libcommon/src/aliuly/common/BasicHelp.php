<?php
namespace aliuly\common;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use aliuly\common\mc;
use aliuly\common\Cli;
use aliuly\common\BasicCli;

/**
 * Implements simple help functionality for sub-commands
 */
class BasicHelp extends BasicCli {
	protected $fmt;
	/**
	 * @param PluginBase $owner - plugin that owns this command
	 */
	public function __construct($owner,$fmt = "/%s %s %s") {
		parent::__construct($owner);
		$this->enableSCmd("help",["aliases"=>["?"]]);
		$this->fmt = $fmt;
	}
	/**
	 * Entry point for sub-commands.  Will show the help or usage messages
	 *
	 * @param CommandSender $c - Entity issuing the command
	 * @param Command $cc - actual command that was issued
	 * @param str $scmd - sub-command being executed
	 * @param mixed $data - Additional data passed to sub-command (global options)
	 * @param str[] $args - arguments for sub-command
	 */
	public function onSCommand(CommandSender $c,Command $cc,$scmd,$data,array $args) {
		$cm = $this->owner->getSCmdMap();
		$pageNumber = $this->getPageNumber($args);

		if (count($args)) {
			if ($args[0] == "usage") {
				if (!isset($cm["usage"][$scmd])) return false;
				$c->sendMessage(TextFormat::RED.mc::_("Usage: ").
									 sprintf($this->fmt,
												$cc->getName(),
												$scmd,
												$cm["usage"][$scmd]));
				return true;
			}
			$txt = [ "Help for ".$cc->getName() ];

			foreach ($args as $i) {
				if (isset($cm["alias"][$i])) $i=$cm["alias"][$i];
				if (!isset($cm["help"][$i]) && !isset($cm["usage"][$i])) {
					$txt[] = TextFormat::RED.mc::_("No help for %1%",$i);
					continue;
				}
				$txt[] = TextFormat::YELLOW.mc::_("Help: ").TextFormat::WHITE.
						 "/".$cc->getName()." $i";
				if (isset($cm["help"][$i]))
					$txt[] = TextFormat::YELLOW.mc::_("Description: ").
							 TextFormat::WHITE.$cm["help"][$i];
				if (isset($cm["usage"][$i]))
					$txt[] = TextFormat::YELLOW.mc::_("Usage: ").
							 TextFormat::WHITE.
							 sprintf($this->fmt,$cc->getName(),$i,$cm["usage"][$i]);
				//echo ">>> ".$this->fmt."\n";//##DEBUG
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
			$ln .= ": ".TextFormat::WHITE.$desc;
			$txt[] = $ln;
		}
		return $this->paginateText($c,$pageNumber,$txt);
	}
}
