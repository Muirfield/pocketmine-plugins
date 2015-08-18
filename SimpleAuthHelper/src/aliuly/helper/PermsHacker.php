<?php
namespace aliuly\helper;
/*
 * Mess with permissions to make sure that the player has permissions to
 * register and login
 */
use pocketmine\event\Listener;

use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

class PermsHacker implements Listener{
	protected $perms;
	public function __construct($plugin) {
		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
	}
	public function forcePerms($player) {
		$n = null;
		foreach (["simpleauth.command.login","simpleauth.command.register"] as $perm) {
			if ($player->hasPermission($perm)) continue;
			if ($n === null) $n = strtolower($player->getName());
			if (!isset($this->perms[$n])) {
				$this->perms[$n] = $player->addAttachment($this);
			}
			$this->perms[$n]->setPermission($perm,true);
		}
		if ($n !== null) $player->recalculatePermissions();
	}
	public function resetPerms($pl) {
		$n = strtolower($pl->getName());
		if (isset($this->perms[$n])) {
			$attach = $this->perms[$n];
			unset($this->perms[$n]);
			$pl->removeAttachment($attach);
		}	
	}
	public function onQuit(PlayerQuitEvent $ev) {
		echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$this->resetPerms($ev->getPlayer());
	}
	public function onCmd(PlayerCommandPreprocessEvent $ev) {
  	echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$this->forcePerms($ev->getPlayer(););
	}
}
