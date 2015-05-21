<?php
/**
 ** OVERVIEW:Basic Usage
 **
 ** COMMANDS
 **
 ** * gm : Configures a world's gamemode
 **   usage : /wp _[world]_ gm _[value]_
 **   - /wp _[world]_ **gm**
 **     - shows the current game mode
 **   - /wp _[world]_ **gm** _mode_
 **     - Sets world game mode
 **   - /wp _[world]_ **gm** **none**
 **     - Removes per world gamemode
 **/
namespace aliuly\worldprotect;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\Player;

class GmMgr extends BaseWp implements Listener {
	public function __construct(Plugin $plugin) {
		parent::__construct($plugin);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$this->enableSCmd("gm",["usage" => "[value]",
										 "help" => "Sets the world game mode",
										 "permission" => "wp.cmd.gm",
										 "aliases" => ["gamemode"]]);
	}
	public function onSCommand(CommandSender $c,Command $cc,$scmd,$world,array $args) {
		if ($scmd != "gm") return false;
		if (count($args) == 0) {
			$gm = $this->owner->getCfg($world, "gamemode", null);
			if ($gm === null) {
				$c->sendMessage("[WP] No gamemode for $world");
			} else {
				$c->sendMessage("[WP] $world Gamemode: ".$this->owner->gamemodeString($gm));
			}
			return true;
		}
		if (count($args) != 1) return false;
		$newmode = $this->owner->getServer()->getGamemodeFromString($args[0]);
		if ($newmode === -1) {
			$this->owner->unsetCfg($world,"gamemode");
			$this->owner->getServer()->broadcastMessage("[WP] $world gamemode removed");
		} else {
			$this->owner->setCfg($world,"gamemode",$newmode);
			$this->owner->getServer()->broadcastMessage("[WP] $world gamemode set to ".
																	  $this->owner->gamemodeString($newmode));
		}
		return true;
	}
	/**
	 * @priority HIGHEST
	 */
	public function onTeleport(EntityTeleportEvent $ev){
		//echo __METHOD__.",".__LINE__."\n"; //##DEBUG
		if ($ev->isCancelled()) return;
		$pl = $ev->getEntity();
		if (!($pl instanceof Player)) return;
		echo __METHOD__.",".__LINE__."\n"; //##DEBUG
		$world = $ev->getTo()->getLevel();
		if (!$world) {
			$world = $pl->getLevel();
		}
		$world = $world->getName();
		$gm = $this->owner->getCfg($world,"gamemode",null);
		if ($gm === null) return;
		if ($pl->hasPermission("wp.cmd.gm.exempt")) return;
		if ($pl->getGamemode() == $gm) return;
		$pl->sendMessage("Changing gamemode to ".
							  $this->owner->gamemodeString($gm));
		$pl->setGamemode($gm);
	}
}
