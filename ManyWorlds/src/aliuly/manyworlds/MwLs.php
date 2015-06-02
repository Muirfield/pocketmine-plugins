<?php
/**
 ** OVERVIEW:Basic Usage
 **
 ** COMMANDS
 **
 ** * ls : Provide world information
 **   usage: /mw **ls** _[world]_
 **
 **   If _world_ is not specified, it will list available worlds.
 **   Otherwise, details for _world_ will be provided.
 **/
namespace aliuly\manyworlds;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\utils\TextFormat;

use aliuly\manyworlds\common\mc;
use aliuly\manyworlds\common\BasicCli;

class MwLs extends BasicCli {
	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableSCmd("ls",["usage" => mc::_("[world]"),
										"help" => mc::_("List world information"),
										"permission" => "mw.cmd.ls",
										"aliases" => ["list","info"]]);
	}

	private function mwWorldList(CommandSender $sender) {
		$dir = $this->owner->getServer()->getDataPath(). "worlds";
		if (!is_dir($dir)) {
			$sender->sendMessage(mc::_("[MW] Missing path %1%",$dir));
			return null;
		}
		$txt = ["HDR"];

		$auto = $this->owner->getServer()->getProperty("worlds",[]);
		$default = $this->owner->getServer()->getDefaultLevel();
		if ($default) $default = $default->getName();

		$count = 0;
		$dh = opendir($dir);
		if (!$dh) return null;
		while (($file = readdir($dh)) !== false) {
			if ($file == '.' || $file == '..') continue;
			if (!$this->owner->getServer()->isLevelGenerated($file)) continue;
			$attrs = [];
			++$count;
			if (isset($auto[$file])) $attrs[] = mc::_("auto");
			if ($default == $file) $attrs[]=mc::_("default");
			if ($this->owner->getServer()->isLevelLoaded($file)) {
				$attrs[] = mc::_("loaded");
				$np = count($this->owner->getServer()->getLevelByName($file)->getPlayers());
				if ($np) $attrs[] = mc::_("players:%1%",$np);
			}
			$ln = "- $file";
			if (count($attrs)) $ln .= TextFormat::AQUA." (".implode(",",$attrs).")";
			$txt[] = $ln;
		}
		closedir($dh);
		$txt[0] = mc::_("Worlds: %1%",$count);
		return $txt;
	}
	private function mwWorldDetails(CommandSender $sender,$world) {
		$txt = [];
		if ($this->owner->getServer()->isLevelLoaded($world)) {
			$unload = false;
		} else {
			if (!$this->owner->autoLoad($sender,$world)) {
				$sender->sendMessage(TextFormat::RED.mc::_("Error getting %1%",$world));
				return null;
			}
			$unload = true;
		}
		$level = $this->owner->getServer()->getLevelByName($world);

		//==== provider
		$provider = $level->getProvider();
		$txt[] = mc::_("Info for %1%",$world);
		$txt[] = TextFormat::AQUA.mc::_("Provider: ").TextFormat::WHITE. $provider::getProviderName();
		$txt[] = TextFormat::AQUA.mc::_("Path: ").TextFormat::WHITE.$provider->getPath();
		$txt[] = TextFormat::AQUA.mc::_("Name: ").TextFormat::WHITE.$provider->getName();
		$txt[] = TextFormat::AQUA.mc::_("Seed: ").TextFormat::WHITE.$provider->getSeed();
		$txt[] = TextFormat::AQUA.mc::_("Generator: ").TextFormat::WHITE.$provider->getGenerator();
		$gopts = $provider->getGeneratorOptions();
		if ($gopts["preset"] != "")
			$txt[] = TextFormat::AQUA.mc::_("Generator Presets: ").TextFormat::WHITE.
					 $gopts["preset"];
		$spawn = $provider->getSpawn();
		$txt[] = TextFormat::AQUA.mc::_("Spawn: ").TextFormat::WHITE.$spawn->getX().",".$spawn->getY().",".$spawn->getZ();
		$plst = $level->getPlayers();
		$lst = "";
		if (count($plst)) {
			foreach ($plst as $p) {
				$lst .= (strlen($lst) ? ", " : "").$p->getName();
			}
		}
		$txt[] = TextFormat::AQUA.mc::_("Players(%1%):",count($plst)).
				 TextFormat::WHITE.$lst;

		// Check for warnings...
		if ($provider->getName() != $world) {
			$txt[] = TextFormat::RED.mc::_("Folder Name and Level.Dat names do NOT match");
			$txt[] = TextFormat::RED.mc::_("This can cause intermitent problems");
			if($sender->hasPermission("mw.cmd.lvdat")) {
				$txt[] = TextFormat::RED.mc::_("Use: ");
				$txt[] = TextFormat::GREEN.mc::_("> /mw fixname %1%",$world);
				$txt[] = TextFormat::RED.mc::_("to fix this issue");
			}
		}

		if ($unload) $this->owner->getServer()->unloadLevel($level);

		return $txt;
	}

	public function onSCommand(CommandSender $c,Command $cc,$scmd,$data,array $args) {
		$pageNumber = $this->getPageNumber($args);
		if (count($args) == 0) {
			$txt = $this->mwWorldList($c);
		} else {
			$wname = implode(" ",$args);
			$txt = $this->mwWorldDetails($c,$wname);
		}
		if ($txt == null) return true;
		return $this->paginateText($c,$pageNumber,$txt);
	}
}
