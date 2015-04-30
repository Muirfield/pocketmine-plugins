<?php
namespace aliuly\worldprotect;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\event\player\PlayerMoveEvent;

class WpBordersMgr extends BaseWp implements Listener {
	public function __construct(Plugin $plugin) {
		parent::__construct($plugin);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$this->enableSCmd("border",["usage" => "[range|none|x1 z1 x2 z2]",
											 "help" => "Creates a border defined\n\tby x1,z1 to x2,z2\n\tUse [none] to remove\n\tIf [range] is specified the border is\n\t-range,-range to range,range\n\taround the spawn point",
											 "permission" => "wp.cmd.border"]);

	}

	public function onSCommand(CommandSender $c,Command $cc,$scmd,$world,array $args) {
		if ($scmd != "border") return false;
		if (count($args) == 0) {
			$limits = $this->owner->getCfg($world,"border",null);
			if ($limits == null) {
				$c->sendMessage("[WP] $world has no borders");
			} else {
				list($x1,$z1,$x2,$z2) = $limits;
				$c->sendMessage("[WP] Border for $world is ($x1,$z1)-($x2,$z2)");
			}
			return true;
		}
		if (count($args) == 1) {
			$range = intval($args[0]);
			if ($range == 0) {
				$this->owner->unsetCfg($world,"border");
				$this->owner->getServer()->broadcastMessage("[WP] Border for $world removed");
				return true;
			}
			if (!$this->owner->getServer()->isLevelLoaded($world)) {
				if (!$this->owner->getServer()->loadLevel($world)) {
					$c->sendMessage("Error loading level $world");
					return true;
				}
				$unload = true;
			} else
				$unload = false;
			$l = $this->owner->getServer()->getLevelByName($world);
			if (!$l) {
				$c->sendMessage("Unable to find level $world");
				return true;
			}
			$pos = $l->getSpawnLocation();
			if ($unload) $this->owner->getServer()->unloadLevel($l);
			$args = [ $pos->getX() - $range, $pos->getZ() - $range,
						 $pos->getX() + $range, $pos->getZ() + $range ];

		}
		if (count($args) == 4) {
			list($x1,$z1,$x2,$z2) = $args;
			if (!is_numeric($x1) || !is_numeric($z1)
				 || !is_numeric($x2) || !is_numeric($z2)) {
				$c->sendMessage("[WP] Invalid border specification");
				return false;
			}
			if ($x1 > $x2) list($x1,$x2) = [$x2,$x1];
			if ($z1 > $z2) list($z1,$z2) = [$z2,$z1];
			$this->owner->setCfg($world,"border",[$x1,$z1,$x2,$z2]);
			$this->owner->getServer()->broadcastMessage("[WP] Border for $world set to ($x1,$z1)-($x2,$z2)");
			return true;
		}
		return false;
	}

	private function checkMove($world,$x,$z) {
		if (!isset($this->wcfg[$world])) return true;
		list($x1,$z1,$x2,$z2) = $this->wcfg[$world];
		if ($x1 < $x && $x < $x2 && $z1 < $z && $z < $z2) return true;
		return false;
	}

	public function onPlayerMove(PlayerMoveEvent $ev) {
		if ($ev->isCancelled()) return;
		$pl = $ev->getPlayer();
		$pos = $ev->getTo();
		if ($this->checkMove($pl->getLevel()->getName(),
									$pos->getX(),$pos->getZ())) return;
		$this->owner->msg($pl,"You have reached the end of the world");
		$ev->setCancelled();
	}
}
