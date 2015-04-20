<?php

namespace aliuly\signwarp;

use pocketmine\plugin\PluginBase;
use pocketmine\command\PluginCommand;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\item\Item;;
use pocketmine\tile\Sign;
use pocketmine\utils\Config;

use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;

use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\scheduler\CallbackTask;


class Main extends PluginBase implements Listener {
	const MAX_COORD = 30000000;
	const MIN_COORD = -30000000;
	const MAX_HEIGHT = 128;
	const MIN_HEIGHT = 0;

	protected $teleporters;
	protected $broadcast;
	protected $text;

	public function onEnable(){
		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
		$defaults =
					 [
						 "settings" => [
							 "dynamic-updates" => true,
							 "broadcast-tp" => true,
							 "xyz.cmd" => true,
						 ],
						 "text" => [
							 "world" => [ "[WORLD]" ],
							 "warp" => [ "[WARP]", "[SWARP]" ],
							 "players" => [ "Players:" ],
						 ]
					 ];

		$cfg = (new Config($this->getDataFolder()."config.yml",
								 Config::YAML,$defaults))->getAll();

		$this->broadcast = $cfg["settings"]["broadcast-tp"];
		$this->text = [ "sign" => [] ];
		foreach (["world","warp"] as $n) {
			$thist->text[$n] = [];
			foreach ($cfg["text"][$n] as $m) {
				$this->text[$n][$m] = $m;
				$this->text["sign"][$m] = $m;
			}
		}
		$this->text["players"] = $cfg["text"]["players"];

		if ($cfg["settings"]["xyz.cmd"]) {
			$newCmd = new PluginCommand("xyz",$this);
			$newCmd->setDescription("Returns x,y,z coordinates");
			$newCmd->setUsage("/xyz");
			$newCmd->setPermission("signwarp.cmd.xyz");
			$cmdMap = $this->getServer()->getCommandMap();
			$cmdMap->registerAll($this->getDescription()->getName(),
										[$newCmd]);
			$this->getLogger()->info("enabled /xyz command");
		}

		if ($cfg["settings"]["dynamic-updates"]) {
			$this->getLogger()->info("dynamic-updates: ON");
			$tt = new CallbackTask([$this,"updateSigns"],[]);
			$this->getServer()->getScheduler()->scheduleRepeatingTask($tt,40);
		} else {
			$this->getLogger()->info("dynamic-updates: OFF");
		}
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	//////////////////////////////////////////////////////////////////////
	//
	// Support functions
	//
	//////////////////////////////////////////////////////////////////////
	private function checkSign(Player $pl,array $sign) {
		if (isset($this->text["warp"][$sign[0]])) {
			// Short warp...
			if (empty($sign[1])) {
				$pl->sendMessage("[SignWarp] No coordinates specified");
				return null;
			}
			$mv = [];
			if ($this->check_coords($sign[1],$mv) !== true) {
				$pl->sendMessage("[SignWarp] Invalid coordinates ".$sign[1]);
				return null;
			}
			return new Position($mv[0],$mv[1],$mv[2],$pl->getLevel());
		}
		// Long warp!
		if (isset($this->text["world"][$sign[0]])) {
			if (empty($sign[1])) {
				$pl->sendMessage("[SignWarp] No World specified");
				return null;
			}
			// Check level...
			if (!$this->getServer()->isLevelGenerated($sign[1])) {
				$pl->sendMessage("[SignWarp] World \"".$sign[1]
									  ."\" does not exist!");
				return null;
			}
			if (!$this->getServer()->isLevelLoaded($sign[1])) {
				$pl->sendMessage("[SignWarp] Loading \"".$sign[1]."\"");
				if (!$this->getServer()->loadLevel($sign[1])) {
					$pl->sendMessage("[SignWarp] Unable to load world \"".
										  $sign[1]."\"");
					return null;
				}
			}
			$l = $this->getServer()->getLevelByName($sign[1]);
			if ($l == null) {
				$pl->sendMessage("[SignWarp] Error loading \"".
									  $sign[1]."\"");
				return null;
			}

			$mv = [];
			if ($this->check_coords($sign[2],$mv)) {
				$mv = new Vector3($mv[0],$mv[1],$mv[2]);
			} else {
				$mv = null;
			}
			return $pos = $l->getSafeSpawn($mv);
		}
		$pl->sendMessage("[SignWarp] INTERNAL ERROR");
		return null;
	}
	private function breakSign(Player $pl,Sign $tile,$msg = "") {
		if ($msg != "") $pl->sendMessage($msg);
		$l = $tile->getLevel();
		$l->setBlockIdAt($tile->getX(),$tile->getY(),$tile->getZ(),Block::AIR);
		$l->setBlockDataAt($tile->getX(),$tile->getY(),$tile->getZ(),0);
		$tile->close();
	}
	private function check_coords($line,array &$vec) {
		$mv = array();
		if (!preg_match('/^\s*(-?\d+)\s+(-?\d+)\s+(-?\d+)\s*$/',$line,$mv)) {
			return false;
		}
		list($line,$x,$y,$z) = $mv;
		if ($x <= self::MIN_COORD || $z <= self::MIN_COORD) return false;
		if ($x >= self::MAX_COORD || $z >= self::MAX_COORD) return false;
		if ($y <= self::MIN_HEIGHT || $y >= self::MAX_HEIGHT) return false;
		$vec = [$x,$y,$z];
		return true;
	}
	private function matchCounter($txt) {
		foreach ($this->text["players"] as $t) {
			if (substr($txt,0,strlen($t)) == $t) return $t;
		}
		return false;
	}
	//////////////////////////////////////////////////////////////////////
	//
	// Internal command
	//
	//////////////////////////////////////////////////////////////////////
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		switch ($cmd->getName()) {
			case "xyz":
				if ($sender instanceof Player) {
					$pos = $sender->getPosition();
					$sender->sendMessage("You are at ".
												intval($pos->getX()).",".
												intval($pos->getY()).",".
												intval($pos->getZ()));
				} else {
					$sender->sendMessage("[SignWarp] This command may only be used in-game");
				}
				return true;
		}
		return false;
	}
	//////////////////////////////////////////////////////////////////////
	//
	// Event Handlers
	//
	//////////////////////////////////////////////////////////////////////
	public function onQuit(PlayerQuitEvent $event) {
		$name = $event->getPlayer()->getName();
		if (isset($this->teleporters[$name])) unset($this->teleporters[$name]);
	}

	public function onBlockPlace(BlockPlaceEvent $event){
		$name = $event->getPlayer()->getName();
		if (isset($this->teleporters[$name])) {
			if (time() - $this->teleporters[$name] < 2)
				$event->setCancelled();
			else
				unset($this->teleporters[$name]);
		}
	}
	public function signChanged(SignChangeEvent $event){
		if($event->getBlock()->getId() != Block::SIGN_POST &&
			$event->getBlock()->getId() != Block::WALL_SIGN) return;
		$pl = $event->getPlayer();
		$tile = $pl->getLevel()->getTile($event->getBlock());
		if(!($tile instanceof Sign))return;
		$sign = $event->getLines();

		if (!isset($this->text["sign"][$sign[0]])) return;

		if(!$pl->hasPermission("signwarp.place.sign")) {
			$this->breakSign($pl,$tile,"You are not allowed to make Warp signs");
			return;
		}
		$pos = $this->checkSign($pl,$sign);
		if ($pos === null) {
			$this->breakSign($pl,$tile);
			return;
		}
		if ($this->broadcast)
			$this->getServer()->broadcastMessage(isset($this->text["world"][$sign[0]]) ?
															 "[SignWarp] Portal to ".
															 $pos->getLevel()->getName().
															 " created by ".
															 $pl->getName() :
															 "[SignWarp] Warp to ".
															 $pos->getX().",".
															 $pos->getY().",".
															 $pos->getZ()." created by ".
															 $pl->getName());
	}
	public function playerTouchIt(PlayerInteractEvent $event){
		if($event->getBlock()->getId() != Block::SIGN_POST &&
			$event->getBlock()->getId() != Block::WALL_SIGN) return;
		$pl = $event->getPlayer();
		$sign = $pl->getLevel()->getTile($event->getBlock());
		if(!($sign instanceof Sign)) return;
		$sign = $sign->getText();
		if (!isset($this->text["sign"][$sign[0]])) return;

		if(!$pl->hasPermission("signwarp.touch.sign")) {
			$pl->sendMessage("Nothing happens...");
			return;
		}
		if ($event->getItem()->getId() == Item::SIGN) {
			// Check if the user is holding a sign this stops teleports
			$pl->sendMessage("Can not teleport while holding sign!");
			return;
		}
		$pos = $this->checkSign($pl,$sign);
		if ($pos === null) return;

		$this->teleporters[$pl->getName()] = time();

		$pl->sendMessage("Teleporting...");
		if (($mw = $this->getServer()->getPluginManager()->getPlugin("ManyWorlds")) != null) {
			$mw->mwtp($pl,$pos);
		} else {
			$pl->teleport($pos);
		}
		if ($this->broadcast)
			$this->getServer()->broadcastMessage($pl->getName()." teleported to ".
															 $pos->getLevel()->getName());
	}
	//////////////////////////////////////////////////////////////////////
	//
	// Timed events
	//
	//////////////////////////////////////////////////////////////////////
	public function updateSigns() {
		foreach ($this->getServer()->getLevels() as $lv) {
			foreach ($lv->getTiles() as $tile) {
				if (!($tile instanceof Sign)) continue;
				$sign = $tile->getText();
				if(!in_array($sign[0],$this->text["world"])) continue;

				if (!($t = $this->matchCounter($sign[3]))) continue;
				if ($this->getServer()->isLevelLoaded($sign[1])) {
					$cnt = count($this->getServer()->getLevelByName($sign[1])->getPlayers());
					$upd = $t.$cnt;
				} else {
					$upd = $t."N/A";
				}
				if ($upd == $sign[3]) continue;
				$tile->setText($sign[0],$sign[1],$sign[2],$upd);
			}
		}
	}
}
