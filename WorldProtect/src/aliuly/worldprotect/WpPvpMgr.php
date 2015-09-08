<?php
namespace aliuly\worldprotect;
//= cmd:pvp,Sub_Commands
//: Controls PvP in a world
//> usage: /wp  _[world]_ **pvp** _[on|off|spawn-off]_
//>   - /wp _[world]_ **pvp** **off**
//:     - no PvP is allowed.
//>   - /wp _[world]_ **pvp** **on**
//:     - PvP is allowed
//>   - /wp _[world]_ **pvp** **spawn-off**
//:     - PvP is allowed except if inside the spawn area.
//:
//= features
//: * Per World PvP

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use aliuly\worldprotect\common\mc;

class WpPvpMgr extends BaseWp implements Listener {
	public function __construct(Plugin $plugin) {
		parent::__construct($plugin);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$this->enableSCmd("pvp",["usage" => mc::_("[on|off|spawn-off]"),
										 "help" => mc::_("Control PvP in world"),
										 "permission" => "wp.cmd.pvp"]);
	}
	public function onSCommand(CommandSender $c,Command $cc,$scmd,$world,array $args) {
		if ($scmd != "pvp") return false;
		if (count($args) == 0) {
			$pvp = $this->owner->getCfg($world, "pvp", true);
			if ($pvp === true) {
				$c->sendMessage(mc::_("[WP] PvP in %1% is %2%",$world,TextFormat::RED.mc::_("ON")));
			} elseif ($pvp === false) {
				$c->sendMessage(mc::_("[WP] PvP in %1% is %2%",$world,TextFormat::GREEN.mc::_("OFF")));
			} else {
				$c->sendMessage(mc::_("[WP] PvP in %1% is %2%",$world,TextFormat::YELLOW.mc::_("Off in Spawn")));
			}
			return true;
		}
		if (count($args) != 1) return false;
		switch (substr(strtolower($args[0]),0,2)) {
			case "sp":
				$this->owner->setCfg($world,"pvp","spawn-off");
				$this->owner->getServer()->broadcastMessage(TextFormat::YELLOW.mc::_("[WP] NO PvP in %1%'s spawn",$world));
				break;
			case "on":
			case "tr":
				$this->owner->unsetCfg($world,"pvp");
				$this->owner->getServer()->broadcastMessage(TextFormat::RED.mc::_("[WP] PvP is allowed in %1%",$world));
				break;
			case "of":
			case "fa":
				$this->owner->setCfg($world,"pvp",false);
				$this->owner->getServer()->broadcastMessage(TextFormat::GREEN.mc::_("[WP] NO PvP in %1%",$world));
				break;
			default:
				return false;
		}
		return true;
	}

	public function onPvP(EntityDamageEvent $ev) {
		if ($ev->isCancelled()) return;
		if(!($ev instanceof EntityDamageByEntityEvent)) return;
		if (!(($pl = $ev->getEntity()) instanceof Player
				&& $ev->getDamager() instanceof Player)) return;
		$world = $pl->getLevel()->getName();
		if (!isset($this->wcfg[$world])) return;
		if ($this->wcfg[$world] !== false) {
			$sp = $pl->getLevel()->getSpawnLocation();
			$dist = $sp->distance($pl);
			if ($dist > $this->owner->getServer()->getSpawnRadius()) return;
		}
		$this->owner->msg($ev->getDamager(),mc::_("You are not allowed to do that here"));
		$ev->setCancelled();
	}
}
