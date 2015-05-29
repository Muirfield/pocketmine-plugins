<?php
namespace aliuly\mtp;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pocketmine\level\Position;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;

class Main extends PluginBase implements CommandExecutor,Listener {
	protected $portals;
	protected $max_dist;
	protected $border;
	protected $center;
	protected $corner;

	private function inGame(CommandSender $sender,$msg = true) {
		if ($sender instanceof Player) return true;
		if ($msg) $sender->sendMessage(TextFormat::RED.
												 "You can only use this command in-game");
		return false;
	}

	public function onEnable(){
		if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$defaults = [
			"version" => $this->getDescription()->getVersion(),
			"max-dist" => 8,
			"border" => Block::NETHER_BRICKS,
			"center" => Block::STILL_WATER,
			"corner" => Block::NETHER_BRICKS_STAIRS,
		];
		$cfg = (new Config($this->getDataFolder()."config.yml",
										  Config::YAML,$defaults))->getAll();
		$this->max_dist = $cfg["max-dist"];
		$this->border = $cfg["border"];
		$this->center = $cfg["center"];
		$this->corner = $cfg["corner"];

		$this->portals=(new Config($this->getDataFolder()."portals.yml",
											Config::YAML,[]))->getAll();
		if ($this->getServer()->getPluginManager()->getPlugin("FastTransfer")){
			$this->getLogger()->info(TextFormat::GREEN."FastTransfer available!");
		}
	}
	private function checkLevel($w) {
		if (!$this->getServer()->isLevelGenerated($w)) return null;
		if (!$this->getServer()->isLevelLoaded($w)) {
			if (!$this->getServer()->loadLevel($w)) return null;
		}
		return $this->getServer()->getLevelByName($w);
	}

	private function checkTarget($args) {
		switch (count($args)) {
			case 1:
				$ft_server = explode(":",$args[0],2);
				if (count($ft_server) == 2 && !empty($ft_server[0]) &&
					 is_numeric($ft_server[1])) {
					// This is a Fast Transfer target!
					return $ft_server;
				}
				list($world) = $args;
				$l = $this->checkLevel($world);
				if ($l) return $l->getSafeSpawn();
				return null;
			case 3:
				list($x,$y,$z) = $args;
				if (is_numeric($x) && is_numeric($y) && is_numeric($z)) {
					return new Vector3($x,$y,$z);
				}
				return null;
			case 4:
				list($world,$x,$y,$z) = $args;
				$l = $this->checkLevel($world);
				if ($l && is_numeric($x) && is_numeric($y) && is_numeric($z)) {
					return new Position($x,$y,$z,$l);
				}
				return null;
		}
		return null;
	}

	protected function targetPos($pos,$dir) {
		$lv = $pos->getLevel();
		for($start=new Vector3($pos->getX(),$pos->getY(),$pos->getZ());
			 $start->distance($pos) < $this->max_dist ;
			 $pos = $pos->add($dir)) {
			$block = $lv->getBlock($pos->floor());
			if ($block->getId() != 0) break;
		}
		while ($block->getId() !=0) {
			$block = $block->getSide(Vector3::SIDE_UP);
		}
		return $block;
	}

	protected function buildPortal($center,$dir) {
		$lv = $center->getLevel();
		$x = $center->getX();
		$y = $center->getY();
		$z = $center->getZ();

		$border = Block::get($this->border);
		$center = Block::get($this->center);

		$x_off = $z_off = 0; $mx_off=0; $mz_off = 0;
		if (abs($dir->getX()) > abs(($dir->getZ()))) {
			$x_off = 0; $z_off = 1;
			$mx_off = 1; $mz_off =0;

			$corner1 = Block::get($this->corner,3);
			$corner2 = Block::get($this->corner,2);
			$corner3 = Block::get($this->corner,7);
			$corner4 = Block::get($this->corner,6);
			$front = Block::get($this->corner,1);
			$back = Block::get($this->corner,0);
		} else {
			$x_off = 1; $z_off = 0;
			$mx_off = 0; $mz_off =1;

			$corner1 = Block::get($this->corner,1);
			$corner2 = Block::get($this->corner,0);
			$corner3 = Block::get($this->corner,5);
			$corner4 = Block::get($this->corner,4);
			$front = Block::get($this->corner,3);
			$back = Block::get($this->corner,2);
		}

		$lv->setBlock(new Vector3($x,$y+4,$z),$border);
		$lv->setBlock(new Vector3($x+$x_off,$y+4,$z+$z_off),$border);
		$lv->setBlock(new Vector3($x-$x_off,$y+4,$z-$z_off),$border);
		$lv->setBlock(new Vector3($x+$x_off*2,$y+4,$z+$z_off*2),$corner1);
		$lv->setBlock(new Vector3($x-$x_off*2,$y+4,$z-$z_off*2),$corner2);

		$lv->setBlock(new Vector3($x+$x_off*2,$y,$z+$z_off*2),$corner3);
		$lv->setBlock(new Vector3($x-$x_off*2,$y,$z-$z_off*2),$corner4);
		$lv->setBlock(new Vector3($x,$y,$z),$center);
		$lv->setBlock(new Vector3($x+$x_off,$y,$z+$z_off),$center);
		$lv->setBlock(new Vector3($x-$x_off,$y,$z-$z_off),$center);

		$lv->setBlock(new Vector3($x+$mx_off,$y,$z+$mz_off),$front);
		$lv->setBlock(new Vector3($x+$mx_off+$x_off,$y,$z+$mz_off+$z_off),$front);
		$lv->setBlock(new Vector3($x+$mx_off-$x_off,$y,$z+$mz_off-$z_off),$front);

		$lv->setBlock(new Vector3($x-$mx_off,$y,$z-$mz_off),$back);
		$lv->setBlock(new Vector3($x-$mx_off+$x_off,$y,$z-$mz_off+$z_off),$back);
		$lv->setBlock(new Vector3($x-$mx_off-$x_off,$y,$z-$mz_off-$z_off),$back);

		for ($i=1;$i<=3;++$i) {
			$lv->setBlock(new Vector3($x-$x_off*2,$y+$i,$z-$z_off*2),$border);
			$lv->setBlock(new Vector3($x+$x_off*2,$y+$i,$z+$z_off*2),$border);
			for($j=-1;$j<=1;++$j) {
				$lv->setBlock(new Vector3($x+$x_off*$j,$y+$i,$z+$z_off*$j),$center);
			}
		}

		$bb1 = [ $x-$x_off, $y, $z-$z_off, $x+$x_off+1, $y+4,$z+$z_off+1 ];
		$bb2 = [ $x-$x_off*2, $y, $z-$z_off*2, $x+$x_off*2+1, $y+5,$z+$z_off*2+1 ];
		return [$bb1,$bb2];
	}

	protected function saveCfg() {
		$yaml=new Config($this->getDataFolder()."portals.yml",Config::YAML,[]);
		$yaml->setAll($this->portals);
		$yaml->save();
	}


	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
		switch($cmd->getName()) {
			case "mtp":
				if (!$this->inGame($sender)) return true;
				$dest = $this->checkTarget($args);
				if (!$dest) {
					$sender->sendMessage(TextFormat::RED."Invalid target for portal");
					return true;
				}
				$bl = $this->targetPos($sender,$sender->getDirectionVector());
				list($bb1,$bb2) = $this->buildPortal($bl,$sender->getDirectionVector());
				$lv = $sender->getLevel()->getName();
				if (!isset($this->portals[$lv])) $this->portals[$lv] = [];
				$this->portals[$lv][] = [ $bb1, $bb2, $args ];
				$this->saveCfg();
				return true;
		}
		return false;
	}

	public function onMove(PlayerMoveEvent $ev) {
		if ($ev->isCancelled()) return;
		$pl = $ev->getPlayer();
		$l = $pl->getLevel();
		$world = $l->getName();

		if (!isset($this->portals[$world])) return;

		$x = $ev->getTo()->getX();
		$y = $ev->getTo()->getY();
		$z = $ev->getTo()->getZ();

		foreach ($this->portals[$world] as $p) {
			list($bb1,$bb2,$target) = $p;
			if ($bb1[0] <= $x && $bb1[1] <= $y && $bb1[2] <= $z &&
				 $x <= $bb1[3] && $y <= $bb1[4] && $z <= $bb1[5]) {

				$dest = $this->checkTarget($target);
				if (!$dest) {
					$pl->sendMessage("Nothing happens!");
					return;
				}
				if ($dest instanceof Vector3) {
					$pl->sendMessage("Teleporting...");
					if (($mw = $this->getServer()->getPluginManager()->getPlugin("ManyWorlds")) != null) {
						$mw->mwtp($pl,$dest);
					} else {
						$pl->teleport($dest);
					}
					return;
				}
				// If it is not a position
				$ft = $this->getServer()->getPluginManager()->getPlugin("FastTransfer");
				if (!$ft) {
					$this->getLogger()->error(TextFormat::RED."FAST TRANSFER NOT INSTALLED");
					$pl->sendMessage("Nothing happens!");
					$pl->sendMessage(TextFormat::RED."Somebody removed FastTransfer!");
					return;
				}
				list($addr,$port) = $dest;
				$this->getLogger()->info(TextFormat::RED."FastTransfer being used hope it works!");
				$this->getLogger()->info("- Player:  ".$pl->getName()." => ".
												 $addr.":".$port);
				$ft->transferPlayer($pl,$addr,$port);
				return;
			}
		}
	}

	/**
	 * @priority HIGH
	 */
	public function onBlockBreak(BlockBreakEvent $ev){
		if ($ev->isCancelled()) return;
		$bl = $ev->getBlock();
		$l = $bl->getLevel();
		if (!$l) return;
		$world = $l->getName();
		if (!isset($this->portals[$world])) return;

		$x = $bl->getX();
		$y = $bl->getY();
		$z = $bl->getZ();

		foreach ($this->portals[$world] as $i=>$p) {
			list($bb1,$bb2,$target) = $p;
			if ($bb2[0] <= $x && $bb2[1] <= $y && $bb2[2] <= $z &&
				 $x <= $bb2[3] && $y <= $bb2[4] && $z <= $bb2[5]) {
				// Breaking a portal!
				$pl = $ev->getPlayer();
				if (($pl instanceof Player) && !$pl->hasPermission("mtp.destroy")) {
					$ev->setCancelled();
					$pl->sendMessage("You are not allowed to do that!");
					return;
				}
				$air = Block::get(Block::AIR);
				for($bx=$bb1[0];$bx<$bb1[3];$bx++) {
					for($by=$bb1[1];$by<$bb1[4];$by++) {
						for($bz=$bb1[2];$bz<$bb1[5];$bz++) {
							$l->setBlock(new Vector3($bx,$by,$bz),$air);
						}
					}
				}
				$pl->sendMessage("Portal broken!");
				unset($this->portals[$world][$i]);
				$this->saveCfg();
				return;
			}
		}
	}
}
