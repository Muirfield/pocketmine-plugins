<?php
namespace aliuly\worldprotect;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\Player;

class MaxPlayerMgr extends BaseWp implements Listener {
	public function __construct(Plugin $plugin) {
		parent::__construct($plugin);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$this->enableSCmd("max",["usage" => "[value]",
										 "help" => "Limits number of players\n\tin a world to [value]\n\tuse 0 or -1 to remove limits",
										 "permission" => "wp.cmd.limit",
										 "aliases" => ["limit"]]);
	}

	public function onSCommand(CommandSender $c,Command $cc,$scmd,$world,array $args) {
		if ($scmd != "max") return false;
		if (count($args) == 0) {
			$count = $this->owner->getCfg($world, "max-players", null);
			if ($count == null) {
				$c->sendMessage("[WP] Max players in $world is un-limited");
			} else {
				$c->sendMessage("[WP] Players allowed in $world: $count");
			}
			return true;
		}
		if (count($args) != 1) return false;
		$count = intval($args[0]);
		if ($count <= 0) {
			$this->owner->unsetCfg($world,"max-players");
			$this->owner->getServer()->broadcastMessage("[WP] Player limit in $world removed");
		} else {
			$this->owner->setCfg($world,"max-players",$count);
			$this->owner->getServer()->broadcastMessage("[WP] Player limit for $world set to $count");
		}
		return true;
	}

	public function onTeleport(EntityTeleportEvent $ev){
		//echo __METHOD__.",".__LINE__."\n"; //##DEBUG
		if ($ev->isCancelled()) return;
		$et = $ev->getEntity();
		if (!($et instanceof Player)) return;

		$from = $ev->getFrom()->getLevel();
		$to = $ev->getTo()->getLevel();
		if (!$from) {
			// THIS SHOULDN'T HAPPEN!
			return;
		}
		if (!$to) {
			// Somebody did not initialize the level properly!
			// But we return because they do not intent to change worlds
			return;
		}

		$from = $from->getName();
		$to = $to->getName();

		//echo "FROM:$from TO:$to\n";//##DEBUG
		if ($from == $to) return;
		$max = $this->getCfg($to,0);
		if ($max == 0) return;
		$np = count($this->owner->getServer()->getLevelByName($to)->getPlayers());
		if($np >= $max) {
			$ev->setCancelled();
			$et->sendMessage("Unable to teleport to $to\nWorld is full");
			$this->owner->getLogger()->info("$to is FULL");
		}
	}
}
