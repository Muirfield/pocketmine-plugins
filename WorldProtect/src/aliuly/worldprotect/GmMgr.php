<?php
//= cmd:gm,Sub_Commands
//: Configures per world game modes
//> usage: /wp _[world]_ gm _[value]_
//:
//: Options:
//> - /wp _[world]_ **gm**
//:   - show current gamemode
//> - /wp _[world]_ **gm** _<mode>_
//:   - Sets the world gamemode to _mode_
//> - /wp _[world]_ **gm** **none**
//:   - Removes per world game mode
//:
//= features
//: * Per world game modes
namespace aliuly\worldprotect;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\Player;
use aliuly\worldprotect\common\MPMU;
use aliuly\worldprotect\common\mc;

class GmMgr extends BaseWp implements Listener {
	public function __construct(Plugin $plugin) {
		parent::__construct($plugin);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$this->enableSCmd("gm",["usage" => mc::_("[value]"),
										"help" => mc::_("Sets the world game mode"),
										"permission" => "wp.cmd.gm",
										"aliases" => ["gamemode"]]);
	}
	public function onSCommand(CommandSender $c,Command $cc,$scmd,$world,array $args) {
		if ($scmd != "gm") return false;
		if (count($args) == 0) {
			$gm = $this->owner->getCfg($world, "gamemode", null);
			if ($gm === null) {
				$c->sendMessage(mc::_("[WP] No gamemode for %1%",$world));
			} else {
				$c->sendMessage(mc::_("[WP] %1% Gamemode: %2%",$world,
											 MPMU::gamemodeStr($gm)));
			}
			return true;
		}
		if (count($args) != 1) return false;
		$newmode = $this->owner->getServer()->getGamemodeFromString($args[0]);
		if ($newmode === -1) {
			$this->owner->unsetCfg($world,"gamemode");
			$this->owner->getServer()->broadcastMessage(mc::_("[WP] %1% gamemode removed", $world));
		} else {
			$this->owner->setCfg($world,"gamemode",$newmode);
			$this->owner->getServer()->broadcastMessage(mc::_("[WP] %1% gamemode set to %2%",
																			  $world,
																			  MPMU::gamemodeStr($newmode)));
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
		if ($pl->hasPermission("wp.cmd.gm.exempt")) return;

		//echo __METHOD__.",".__LINE__."\n"; //##DEBUG
		$world = $ev->getTo()->getLevel();
		if (!$world) {
			$world = $pl->getLevel();
		}
		$world = $world->getName();
		$gm = $this->owner->getCfg($world,"gamemode",null);
		if ($gm === null) {
			$gm = $this->owner->getServer()->getGamemode();
		}
		if ($pl->getGamemode() == $gm) return;
		$pl->sendMessage(mc::_("Changing gamemode to %1%",
									  MPMU::gamemodeStr($gm)));
		$pl->setGamemode($gm);
	}
}
