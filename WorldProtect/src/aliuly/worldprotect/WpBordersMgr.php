<?php
//= cmd:border,Sub_Commands
//: defines a border for a world
//> usage: /wp  _[world]_ **border** _[range|none|x1 z1 x2 z2]_
//:
//: Defines a border for an otherwise infinite world.  Usage:
//>   - /wp _[world]_ **border**
//:     - will show the current borders for _[world]_.
//>   - /wp _[world]_ **border** _x1 z1 x2 z2_
//:     - define the border as the region defined by _x1,z1_ and _x2,z2_.
//>   - /wp _[world]_ **border** _range_
//:     - define the border as being _range_ blocks in `x` and `z` axis away
//:       from the spawn point.
//>   - /wp _[world]_ **border** **none**
//:     - Remove borders
//:
//= features
//: * World borders

//= docs
//: It is possible to create limits in your limitless worlds.
//: So players are not able to go beyond a preset border.  This is
//: useful if you want to avoid overloading the server by
//: generating new Terrain.
//:


namespace aliuly\worldprotect;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use aliuly\worldprotect\common\mc;

use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\entity\EntityTeleportEvent;

class WpBordersMgr extends BaseWp implements Listener {
	public function __construct(Plugin $plugin) {
		parent::__construct($plugin);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$this->enableSCmd("border",["usage" => mc::_("[range|none|x1 z1 x2 z2]"),
											 "help" => mc::_("Creates a border defined\n\tby x1,z1 to x2,z2\n\tUse [none] to remove\n\tIf [range] is specified the border is\n\t-range,-range to range,range\n\taround the spawn point"),
											 "permission" => "wp.cmd.border"]);

	}

	public function onSCommand(CommandSender $c,Command $cc,$scmd,$world,array $args) {
		if ($scmd != "border") return false;
		if (count($args) == 0) {
			$limits = $this->owner->getCfg($world,"border",null);
			if ($limits == null) {
				$c->sendMessage(mc::_("[WP] %1% has no borders",$world));
			} else {
				list($x1,$z1,$x2,$z2) = $limits;
				$c->sendMessage(mc::_("[WP] Border for %1% is (%2%,%3%)-(%4%,%5%)",
											 $world,$x1,$z1,$x2,$z2));
			}
			return true;
		}
		if (count($args) == 1) {
			$range = intval($args[0]);
			if ($range == 0) {
				$this->owner->unsetCfg($world,"border");
				$this->owner->getServer()->broadcastMessage(mc::_("[WP] Border for %1% removed",$world));
				return true;
			}
			if (!$this->owner->getServer()->isLevelLoaded($world)) {
				if (!$this->owner->getServer()->loadLevel($world)) {
					$c->sendMessage(mc::_("Error loading level %1%",$world));
					return true;
				}
				$unload = true;
			} else
				$unload = false;
			$l = $this->owner->getServer()->getLevelByName($world);
			if (!$l) {
				$c->sendMessage(mc::_("Unable to find level %1%",$world));
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
				$c->sendMessage(mc::_("[WP] Invalid border specification"));
				return false;
			}
			if ($x1 > $x2) list($x1,$x2) = [$x2,$x1];
			if ($z1 > $z2) list($z1,$z2) = [$z2,$z1];
			$this->owner->setCfg($world,"border",[$x1,$z1,$x2,$z2]);
			$this->owner->getServer()->broadcastMessage(mc::_("[WP] Border for %1% set to (%2%,%3%)-(%4%,%5%)", $world, $x1,$z1, $x2, $z2));
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
		$this->owner->msg($pl,mc::_("You have reached the end of the world"));
		$ev->setCancelled();
	}

	public function onTeleport(EntityTeleportEvent $ev){
		if ($ev->isCancelled()) return;
		$pl = $ev->getEntity();
		if (!($pl instanceof Player)) return;
		$to = clone $ev->getTo();
		if (!$to) return;// This should never happen!
		if ($to->getLevel()) {
			$world = $to->getLevel()->getName();
		} else {
			$from = $ev->getFrom();
			if (!$from) return; // OK, this would be weird...
			if (!$from->getLevel()) return; // Can't determine the level at all!
			$world = $from->getLevel()->getName();
		}
		//echo __METHOD__.",".__LINE__."world=$world\n"; //##DEBUG
		if ($this->checkMove($world,$to->getX(),$to->getZ())) return;
		$this->owner->msg($pl,mc::_("You are teleporting outside the world"));
		$ev->setCancelled();
	}

}
