<?php
/**
 ** CONFIG:config.yml
 **/
namespace aliuly\signwarp;

use pocketmine\plugin\PluginBase;
use pocketmine\command\PluginCommand;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\tile\Sign;
use pocketmine\utils\Config;

use pocketmine\utils\TextFormat;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;

use pocketmine\level\Position;
use pocketmine\math\Vector3;
use aliuly\signwarp\common\PluginCallbackTask;
use aliuly\signwarp\common\mc;
use aliuly\signwarp\common\MPMU;

class Main extends PluginBase implements Listener {
	const MAX_COORD = 30000000;
	const MIN_COORD = -30000000;
	const MAX_HEIGHT = 128;
	const MIN_HEIGHT = 0;

	protected $teleporters;
	protected $text;

	public function onEnable(){
		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
		mc::plugin_init($this,$this->getFile());
		$wp = $this->getServer()->getPluginManager()->getPlugin("WorldProtect");
		if ($wp !== null && version_compare($wp->getDescription()->getVersion(),"2.1.0") < 0) {
			$this->getLogger()->warning(TextFormat::RED.mc::_("This version of SignWarp requires"));
			$this->getLogger()->warning(TextFormat::RED.mc::_("at least version 2.1.0 of WorldProtect"));
			$this->getLogger()->warning(TextFormat::RED.mc::_("Only version %1% available",$wp->getDescription()->getVersion()));
			throw new \RuntimeException("Runtime checks failed");
			return;
		}
		$defaults =
					 [
						 "version" => $this->getDescription()->getVersion(),
						 "# settings" => "configurable variables",
						 "settings" => [
							 "# dynamic updates" => "Signs will be udpated with the number of players in a world",
							 "dynamic-updates" => true,
							 "# xyz.cmd" => "If true, the **xyz** command will be available",
							 "xyz.cmd" => true,
						 ],
						 "# text" => "Text displayed on the different signs",
						 "text" => [
							 "# transfer" => "Fast transfer signs",
							 "transfer" => [ "[TRANSFER]" ],
							 "# world" => "World teleport signs",
							 "world" => [ "[WORLD]" ],
							 "# warp" => "Local world teleport signs",
							 "warp" => [ "[WARP]", "[SWARP]" ],
							 "# players" => "Text to use when displaying player counts",
							 "players" => [ "Players:" ],
						 ]
					 ];

		$cfg = (new Config($this->getDataFolder()."config.yml",
								 Config::YAML,$defaults))->getAll();

		if ($this->getServer()->getPluginManager()->getPlugin("FastTransfer")){
			$this->getLogger()->info(TextFormat::GREEN.mc::_("Enabling FastTransfer support"));
		}else{
			$this->getLogger()->warning(TextFormat::BLUE.mc::_("Disabling FastTransfer support"));
			$cfg["text"]["transfer"] = [];
		}

		$this->text = [ "sign" => [] ];
		foreach (["world","warp","transfer"] as $n) {
			$thist->text[$n] = [];
			foreach ($cfg["text"][$n] as $m) {
				$this->text[$n][$m] = $m;
				$this->text["sign"][$m] = $m;
			}
		}
		$this->text["players"] = $cfg["text"]["players"];

		if ($cfg["settings"]["xyz.cmd"]) {
			$newCmd = new PluginCommand("xyz",$this);
			$newCmd->setDescription(mc::_("Returns x,y,z coordinates"));
			$newCmd->setUsage(mc::_("/xyz"));
			$newCmd->setPermission("signwarp.cmd.xyz");
			$cmdMap = $this->getServer()->getCommandMap();
			$cmdMap->registerAll($this->getDescription()->getName(),
										[$newCmd]);
			$this->getLogger()->info(TextFormat::GREEN.mc::_("enabled /xyz command"));
		}

		if ($cfg["settings"]["dynamic-updates"]) {
			$this->getLogger()->info(TextFormat::GREEN.mc::_("dynamic-updates: ON"));
			$tt = new PluginCallbackTask($this,[$this,"updateSigns"],[]);
			$this->getServer()->getScheduler()->scheduleRepeatingTask($tt,40);
		} else {
			$this->getLogger()->info(TextFormat::YELLOW.mc::_("dynamic-updates: OFF"));
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
				$pl->sendMessage(mc::_("[SignWarp] No coordinates specified"));
				return null;
			}
			$mv = [];
			if ($this->check_coords($sign[1],$mv) !== true) {
				$pl->sendMessage(mc::_("[SignWarp] Invalid coordinates %1%",$sign[1]));
				return null;
			}
			return new Position($mv[0],$mv[1],$mv[2],$pl->getLevel());
		}
		// Long warp!
		if (isset($this->text["world"][$sign[0]])) {
			if (empty($sign[1])) {
				$pl->sendMessage(mc::_("[SignWarp] No World specified"));
				return null;
			}
			// Check level...
			if (!$this->getServer()->isLevelGenerated($sign[1])) {
				$pl->sendMessage(mc::_("[SignWarp] World \"%1%\" does not exist!",$sign[1]));

				return null;
			}
			if (!$this->getServer()->isLevelLoaded($sign[1])) {
				$pl->sendMessage(mc::_("[SignWarp] Loading \"%1%\"",$sign[1]));
				if (!$this->getServer()->loadLevel($sign[1])) {
					$pl->sendMessage(mc::_("[SignWarp] Unable to load world \"%1%\"",$sign[1]));
					return null;
				}
			}
			$l = $this->getServer()->getLevelByName($sign[1]);
			if ($l == null) {
				$pl->sendMessage(mc::_("[SignWarp] Error loading \"%1%\"",
											  $sign[1]));
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
		if (isset($this->text["transfer"][$sign[0]])) {
			$address = $sign[1];
			$port = $sign[2];
			if (empty($address)) return null;
			$port = intval($port);
			if ($port == 0) $port = 19132; // Default for Minecraft PE
			return [$address,$port];
		}
		$pl->sendMessage(mc::_("[SignWarp] INTERNAL ERROR"));
		return null;
	}
	public function doBreakSign($tile) {
		$l = $tile->getLevel();
		$l->setBlockIdAt($tile->getX(),$tile->getY(),$tile->getZ(),Block::AIR);
		$l->setBlockDataAt($tile->getX(),$tile->getY(),$tile->getZ(),0);
		$tile->close();
	}
	public function breakSign(Player $pl,Sign $tile,$msg = "") {
		if ($msg != "") $pl->sendMessage($msg);
		$this->getServer()->getScheduler()->scheduleDelayedTask(
				new PluginCallbackTask($this,[$this,"doBreakSign"],[$tile]),10
		);
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
					$sender->sendMessage(mc::_("You are at %1%,%2%,%3%",
														intval($pos->getX()),
														intval($pos->getY()),
														intval($pos->getZ())));
				} else {
					$sender->sendMessage(TextFormat::RED.mc::_("[SignWarp] This command may only be used in-game"));
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
			$this->breakSign($pl,$tile,mc::_("You are not allowed to make Warp signs"));
			return;
		}
		// FastTransfer!
		if (isset($this->text["transfer"][$sign[0]])) {
			// Fast transfer!
			if(!$pl->hasPermission("signwarp.place.transfer.sign")) {
				$this->breakSign($pl,$tile,mc::_("You are not allowed to make\nTransfer Warp signs"));
				return;
			}
		}

		$pos = $this->checkSign($pl,$sign);
		if ($pos === null) {
			$this->breakSign($pl,$tile);
			return;
		}
		if ($pos instanceof Position) {
			$this->getServer()->broadcastMessage(
				isset($this->text["world"][$sign[0]]) ?
				mc::_("[SignWarp] Portal to %1% created by %2%",
						$pos->getLevel()->getName(),$pl->getName()) :
				mc::_("[SignWarp] Warp to %1%,%2%,%3% created by %4%",
						$pos->getX(),$pos->getY(),$pos->getZ(),
						$pl->getName()));
		} else {
			$this->getServer()->broadcastMessage(
				mc::_("[SignWarp] Transfer portal %1% created by %2%",
						implode(":",$pos), $pl->getName()));
		}
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
			$pl->sendMessage(mc::_("Nothing happens..."));
			return;
		}
		if ($event->getItem()->getId() == Item::SIGN) {
			// Check if the user is holding a sign this stops teleports
			$pl->sendMessage(mc::_("Can not teleport while holding sign!"));
			return;
		}
		$pos = $this->checkSign($pl,$sign);
		if ($pos === null) return;

		if ($pos instanceof Position) {
			$this->teleporters[$pl->getName()] = time();

			$pl->sendMessage(mc::_("Teleporting..."));
			$pl->teleport($pos);
			return;
		}
		// FastTransfer
		if(!$pl->hasPermission("signwarp.touch.transfer.sign")) {
			$pl->sendMessage(mc::_("Did you expect something to happen?"));
			return;
		}
		$this->teleporters[$pl->getName()] = time();
		$ft = $this->getServer()->getPluginManager()->getPlugin("FastTransfer");
		if (!$ft) {
			$this->getLogger()->error(mc::_("FAST TRANSFER NOT INSTALLED"));
			$pl->sendMessage(TextFormat::RED.mc::_("Nothing happens!"));
			$pl->sendMessage(TextFormat::RED.mc::_("Somebody removed FastTransfer!"));
			return;
		}
		list($addr,$port) = $pos;
		$this->getLogger()->info(mc::_("FastTransfer being used hope it works!"));
		$this->getLogger()->info(mc::_("- Player: %1% => %2%:%3%",
												 $pl->getName(),$addr,$port));
		$ft->transferPlayer($pl,$addr,$port);
	}
	//////////////////////////////////////////////////////////////////////
	//
	// Timed events
	//
	//////////////////////////////////////////////////////////////////////
	public function updateSigns() {
		$wp = $this->getServer()->getPluginManager()->getPlugin("WorldProtect");
		foreach ($this->getServer()->getLevels() as $lv) {
			foreach ($lv->getTiles() as $tile) {
				if (!($tile instanceof Sign)) continue;
				$sign = $tile->getText();
				if(!in_array($sign[0],$this->text["world"])) continue;

				if (!($t = $this->matchCounter($sign[3]))) continue;
				if (($lv = $this->getServer()->getLevelByName($sign[1])) !== null) {
					$cnt = count($lv->getPlayers());
					$max = null;
					if ($wp !== null) $max = $wp->getMaxPlayers($lv->getName());
					if ($max == null)
						$upd = $t. TextFormat::BLUE . $cnt;
					else
						$upd = $t . ($cnt>=$max ? TextFormat::RED : TextFormat::GREEN).
									$cnt . "/" . $max;

				} else {
					$upd = $t.mc::_("N/A");
				}
				if ($upd == $sign[3]) continue;
				$tile->setText($sign[0],$sign[1],$sign[2],$upd);
			}
		}
	}
}
