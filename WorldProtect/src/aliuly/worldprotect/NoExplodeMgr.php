<?php
//= cmd:noexplode,Sub_Commands
//: Stops explosions in a world
//> usage: /wp  _[world]_ **noexplode** _[off|world|spawn]_
//>   - /wp _[world]_ **noexplode** **off**
//:     - no-explode feature is `off`, so explosions are allowed.
//>   - /wp _[world]_ **noexplode** **world**
//:     - no explosions allowed in the whole _world_.
//>   - /wp _[world]_ **noexplode** **spawn**
//:     - no explosions allowed in the world's spawn area.
//:
//= features
//: * Control explosions per world
namespace aliuly\worldprotect;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use pocketmine\event\entity\EntityExplodeEvent;
use aliuly\worldprotect\common\mc;

class NoExplodeMgr extends BaseWp implements Listener {
	public function __construct(Plugin $plugin) {
		parent::__construct($plugin);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$this->enableSCmd("noexplode",["usage" => mc::_("[off|world|spawn]"),
												 "help" => mc::_("Disable explosions in world or spawn area"),
												 "permission" => "wp.cmd.noexplode",
												 "aliases" => ["notnt"]]);
	}
	public function onSCommand(CommandSender $c,Command $cc,$scmd,$world,array $args) {
		if ($scmd != "noexplode") return false;
		if (count($args) == 0) {
			$notnt = $this->owner->getCfg($world, "no-explode", false);
			if ($notnt == "world") {
				$c->sendMessage(TextFormat::GREEN.mc::_("[WP] Explosions stopped in %1%",$world));
			} elseif ($notnt == "spawn") {
				$c->sendMessage(TextFormat::YELLOW.mc::_("[WP] Explosions off in %1%'s spawn",$world));
			} else {
				$c->sendMessage(TextFormat::RED.mc::_("[WP] Explosions allowed in %1%",$world));
			}
			return true;
		}
		if (count($args) != 1) return false;
		switch (substr(strtolower($args[0]),0,2)) {
			case "sp":
				$this->owner->setCfg($world,"no-explode","spawn");
				$this->owner->getServer()->broadcastMessage(TextFormat::YELLOW.mc::_("[WP] NO Explosions in %1%'s spawn",$world));
				break;
			case "wo":
				$this->owner->setCfg($world,"no-explode","world");
				$this->owner->getServer()->broadcastMessage(TextFormat::GREEN.mc::_("[WP] NO Explosions in %1%",$world));
				break;
			case "off":
				$this->owner->unsetCfg($world,"no-explode");
				$this->owner->getServer()->broadcastMessage(TextFormat::RED.mc::_("[WP] Explosions Allowed in %1%",$world));
				break;
			default:
				return false;
		}
		return true;
	}
	public function onExplode(EntityExplodeEvent $ev){
		//echo __METHOD__.",".__LINE__."\n";
		if ($ev->isCancelled()) return;
		$et = $ev->getEntity();
		$world = $et->getLevel()->getName();
		if (!isset($this->wcfg[$world])) return;
		if ($this->wcfg[$world] == "spawn") {
			$sp = $et->getLevel()->getSpawnLocation();
			$dist = $sp->distance($et);
			if ($dist > $this->owner->getServer()->getSpawnRadius()) return;
		}
		$ev->setCancelled();
		$this->owner->getLogger()->notice(TextFormat::RED.
												  mc::_("Explosion was stopped in %1%",$world));
	}
}
