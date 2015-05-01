<?php
namespace aliuly\worldprotect;
/**
 ** OVERVIEW:Basic Usage
 **
 ** COMMANDS
 **
 ** * pvp : Controls PvP in a world
 **   usage: /wp  _[world]_ **pvp** _[on|off|spawn-off]_
 **   - /wp _[world]_ **pvp** **off**
 **     - no PvP is allowed.
 **   - /wp _[world]_ **pvp** **on**
 **     - PvP is allowed
 **   - /wp _[world]_ **pvp** **spawn-off**
 **     - PvP is allowed except if inside the spawn area.
 **/

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class WpPvpMgr extends BaseWp implements Listener {
	public function __construct(Plugin $plugin) {
		parent::__construct($plugin);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$this->enableSCmd("pvp",["usage" => "[on|off|spawn-off]",
										 "help" => "Control PvP in world",
										 "permission" => "wp.cmd.pvp"]);
	}
	public function onSCommand(CommandSender $c,Command $cc,$scmd,$world,array $args) {
		if ($scmd != "pvp") return false;
		if (count($args) == 0) {
			$pvp = $this->owner->getCfg($world, "pvp", true);
			if ($pvp === true) {
				$c->sendMessage("[WP] PvP in $world is ".TextFormat::RED."ON");
			} elseif ($pvp === false) {
				$c->sendMessage("[WP] PvP in $world is ".TextFormat::GREEN."OFF");
			} else {
				$c->sendMessage("[WP] PvP in $world is ".TextFormat::YELLOW.
									 "Off in Spawn");
			}
			return true;
		}
		if (count($args) != 1) return false;
		switch (substr(strtolower($args[0]),0,2)) {
			case "sp":
				$this->owner->setCfg($world,"pvp","spawn-off");
				$this->owner->getServer()->broadcastMessage(TextFormat::YELLOW."[WP] NO PvP in $world's spawn");
				break;
			case "on":
			case "tr":
				$this->owner->unsetCfg($world,"pvp");
				$this->owner->getServer()->broadcastMessage(TextFormat::RED."[WP] PvP is allowed in $world");
				break;
			case "of":
			case "fa":
				$this->owner->setCfg($world,"pvp",false);
				$this->owner->getServer()->broadcastMessage(TextFormat::GREEN."[WP] NO PvP in $world");
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
		$this->owner->msg($ev->getDamager(),"You are not allowed to do that here");
		$ev->setCancelled();
	}
}
