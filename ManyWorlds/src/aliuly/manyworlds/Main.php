<?php
/**
 **
 **/
namespace aliuly\manyworlds;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\level\Position;

use pocketmine\utils\TextFormat;

//use pocketmine\level\Level;
//use pocketmine\event\level\LevelLoadEvent;
//use pocketmine\event\level\LevelUnloadEvent;
//use pocketmine\Player;

use aliuly\manyworlds\common\mc;
use aliuly\manyworlds\common\MPMU;
use aliuly\manyworlds\common\BasicPlugin;
use aliuly\manyworlds\common\BasicHelp;

class Main extends BasicPlugin implements CommandExecutor {
	public $canUnload = false;
	private $tpMgr = null;

	public function onEnable() {
		// We don't really need this...
		//if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
		mc::plugin_init($this,$this->getFile());

		if (MPMU::apiVersion("1.12.0")) {
			$this->canUnload = true;
			$this->tpMgr = null;
		} else {
			$this->canUnload = false;
			$this->tpMgr = new TeleportManager($this);
		}
		$this->modules = [];
		foreach ([
			"MwTp",
			"MwLs",
			"MwCreate",
			"MwGenLst",
			"MwLoader",
			"MwLvDat",
			"MwDefault",
		] as $mod) {
			$mod = __NAMESPACE__."\\".$mod;
			$this->modules[] = new $mod($this);
		}
		$this->modules[] = new BasicHelp($this);
	}

	public function autoLoad(CommandSender $c,$world) {
		if ($this->getServer()->isLevelLoaded($world)) return true;
		if($c !== null && !MPMU::access($c, "mw.cmd.world.load")) return false;
		if(!$this->getServer()->isLevelGenerated($world)) {
			if ($c !== null) {
				$c->sendMessage(mc::_("[MW] No world with the name %1% exists!",
											 $world));
			}
			return false;
		}
		$this->getServer()->loadLevel($world);
		return $this->getServer()->isLevelLoaded($world);
	}

	//////////////////////////////////////////////////////////////////////
	//
	// Command dispatcher
	//
	//////////////////////////////////////////////////////////////////////
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
		if ($cmd->getName() != "manyworlds") return false;
		return $this->dispatchSCmd($sender,$cmd,$args);
	}
	//
	// Deprecated Public API
	//
	public function mwtp($pl,$pos) {
		if ($this->tpMgr && ($pos instanceof Position)) {
			// Using ManyWorlds for teleporting...
			return $this->teleport($pl,$pos->getLevel()->getName(),
										  new Vector3($pos->getX(),
														  $pos->getY(),
														  $pos->getZ()));
		}
		$pl->teleport($pos);
		return true;
	}
	public function teleport($player,$world,$spawn=null) {
		if ($this->tpMgr) {
			return $this->tpMgr->teleport($player,$world,$spawn);
		}
		if (!$this->getServer()->isLevelLoaded($world)) return false;
		$level = $this->owner->getServer()->getLevelByName($world);
		if (!$level) return false;
		// Try to find a reasonable spawn location
		$location = $level->getSafeSpawn($spawn);
		$player->teleport($location);
	}
}
