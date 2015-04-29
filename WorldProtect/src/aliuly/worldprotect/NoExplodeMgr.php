<?php
namespace aliuly\worldprotect;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use pocketmine\event\entity\EntityExplodeEvent;

class NoExplodeMgr extends BaseWp implements Listener {
	public function __construct(Plugin $plugin) {
		parent::__construct($plugin);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$this->enableSCmd("noexplode",["usage" => "[off|world|spawn]",
												 "help" => "Disable explosions in world or spawn area",
												 "permission" => "wp.cmd.noexplode",
												 "aliases" => ["notnt"]]);
	}
	public function onSCommand(CommandSender $c,Command $cc,$scmd,$world,array $args) {
		if ($scmd != "noexplode") return false;
		if (count($args) == 0) {
			$notnt = $this->owner->getCfg($world, "no-explode", false);
			if ($notnt == "world") {
				$c->sendMessage(TextFormat::GREEN."[WP] Explosions stopped in $world");
			} elseif ($notnt == "spawn") {
				$c->sendMessage(TextFormat::YELLOW."[WP] Explosions off in $world's spawn");
			} else {
				$c->sendMessage(TextFormat::RED."[WP] Explosions allowed in $world");
			}
			return true;
		}
		if (count($args) != 1) return false;
		switch (substr(strtolower($args[0]),0,2)) {
			case "sp":
				$this->owner->setCfg($world,"no-explode","spawn");
				$this->owner->getServer()->broadcastMessage(TextFormat::YELLOW."[WP] NO Explosions in $world's spawn");
				break;
			case "wo":
				$this->owner->setCfg($world,"no-explode","world");
				$this->owner->getServer()->broadcastMessage(TextFormat::GREEN."[WP] NO Explosions in $world");
				break;
			case "off":
				$this->owner->unsetCfg($world,"no-explode");
				$this->owner->getServer()->broadcastMessage(TextFormat::RED."[WP] Explosions Allowed in $world");
				break;
			default:
				return false;
		}
		return true;
	}
	public function onExplode(EntityExplodeEvent $ev){
		echo __METHOD__.",".__LINE__."\n";
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
		$this->owner->getLogger()->info(TextFormat::RED.
												  "Explosion was stopped in $world");
	}
}
