<?php
namespace aliuly\common;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\command\PluginCommand;

use pocketmine\utils\TextFormat;

abstract class BasicCli {
	protected $owner;
	public function __construct($owner) {
		$this->owner = $owner;
	}
	public function enableSCmd($cmd,$opts) {
		$this->owner->registerScmd($cmd,[$this,"onSCommand"],$opts);
	}
	public function enableCmd($cmd,$yaml) {
		$newCmd = new PluginCommand($cmd,$this->owner);
		if (isset($yaml["description"]))
			$newCmd->setDescription($yaml["description"]);
		if (isset($yaml["usage"]))
			$newCmd->setUsage($yaml["usage"]);
		if(isset($yaml["aliases"]) and is_array($yaml["aliases"])) {
			$aliasList = [];
			foreach($yaml["aliases"] as $alias) {
				if(strpos($alias,":")!== false) {
					$this->owner->getLogger()->info("Unable to load alias $alias");
					continue;
				}
				$aliasList[] = $alias;
			}
			$newCmd->setAliases($aliasList);
		}
		if(isset($yaml["permission"]))
			$newCmd->setPermission($yaml["permission"]);
		if(isset($yaml["permission-message"]))
			$newCmd->setPermissionMessage($yaml["permission-message"]);
		$newCmd->setExecutor($this);
		$cmdMap = $this->owner->getServer()->getCommandMap();
		$cmdMap->register($this->owner->getDescription()->getName(),$newCmd);
	}

	// Paginate output
	protected function getPageNumber(array &$args) {
		$pageNumber = 1;
		if (count($args) && is_numeric($args[count($args)-1])) {
			$pageNumber = (int)array_pop($args);
			if($pageNumber <= 0) $pageNumber = 1;
		}
		return $pageNumber;
	}
	protected function paginateText(CommandSender $sender,$pageNumber,array $txt) {
		$hdr = array_shift($txt);
		if($sender instanceof ConsoleCommandSender){
			$sender->sendMessage( TextFormat::GREEN.$hdr.TextFormat::RESET);
			foreach ($txt as $ln) $sender->sendMessage($ln);
			return true;
		}
		$pageHeight = 5;
		$lineCount = count($txt);
		$pageCount = intval($lineCount/$pageHeight) + ($lineCount % $pageHeight ? 1 : 0);
		$hdr = TextFormat::GREEN.$hdr. TextFormat::RESET;
		if ($pageNumber > $pageCount) {
			$sender->sendMessage($hdr);
			$sender->sendMessage("Only $pageCount pages available");
			return true;
		}
		$hdr .= TextFormat::RED." ($pageNumber of $pageCount)";
		$sender->sendMessage($hdr);
		for ($ln = ($pageNumber-1)*$pageHeight;$ln < $lineCount && $pageHeight--;++$ln) {
			$sender->sendMessage($txt[$ln]);
		}
		return true;
	}
	protected function paginateTable(CommandSender $sender,$pageNumber,array $tab) {
		$cols = [];
		for($i=0;$i < count($tab[0]);$i++) $cols[$i] = strlen($tab[0][$i]);
		foreach ($tab as $row) {
			for($i=0;$i < count($row);$i++) {
				if (($l=strlen($row[$i])) > $cols[$i]) $cols[$i] = $l;
			}
		}
		$txt = [];
		$fmt = "";
		foreach ($cols as $c) {
			if (strlen($fmt) > 0) $fmt .= " ";
			$fmt .= "%-".$c."s";
		}
		foreach ($tab as $row) {
			$txt[] = sprintf($fmt,...$row);
		}
		return $this->paginateText($sender,$pageNumber,$txt);
	}
}
