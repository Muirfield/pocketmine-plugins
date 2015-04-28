<?php
namespace aliuly\worldprotect;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;

use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;

//use pocketmine\event\player\PlayerInteractEvent; // Not used for now...
//use pocketmine\event\entity\EntityExplodeEvent; // Also not used...

class WpProtectMgr extends BaseWp implements Listener {
	public function __construct(Plugin $plugin) {
		parent::__construct($plugin);
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
		$this->enableSCmd("add",["usage" => "<user>",
										 "help" => "Add <user> to authorized list",
										 "permission" => "wp.cmd.addrm"]);
		$this->enableSCmd("rm",["usage" => "<user>",
										"help" => "Remove <user> from authorized list",
										"permission" => "wp.cmd.addrm"]);
		$this->enableSCmd("unlock",["usage" => "",
											 "help" => "Unprotects world",
											 "permission" => "wp.cmd.protect",
											 "aliases" => ["unprotect","open"]]);
		$this->enableSCmd("lock",["usage" => "",
										  "help" => "Locked\n\tNobody (including op) can build",
										  "permission" => "wp.cmd.protect"]);
		$this->enableSCmd("protect",["usage" => "",
											  "help" => "Only authorized (or op) can build",
											  "permission" => "wp.cmd.protect"]);
	}

	public function onSCommand(CommandSender $c,Command $cc,$scmd,$world,array $args) {
		switch ($scmd) {
			case "add":
				if (!count($args)) return false;
				foreach ($args as $i) {
					$player = $this->owner->getServer()->getPlayer($i);
					if (!$player) {
						$c->sendMessage("[WP] $i: not found");
						continue;
					}
					$iusr = strtolower($player->getName());
					$this->owner->authAdd($world,$iusr);
					$c->sendMessage("[WP] $i added to $world's auth list");
					$player->sendMessage("[WP] You have been added to");
					$player->sendMessage("[WP] $world's auth list");
				}
				return true;
			case "rm":
				if (!count($args)) return false;
				foreach ($args as $i) {
					$iusr = strtolower($i);
					if ($this->owner->authCheck($world,$iusr)) {
						$this->owner->authRm($world,$iusr);
						$c->sendMessage("[WP] $i removed from $world's auth list");
						$player = $this->owner->getServer()->getPlayer($i);
						if ($player) {
							$player->sendMessage("[WP] You have been removed from");
							$player->sendMessage("[WP] $world's auth list");
						}
					} else {
						$c->sendMessage("[WP] $i not known");
					}
				}
				return true;
			case "unlock":
				if (count($args)) return false;
				$this->owner->unsetCfg($world,"protect");
				$this->owner->getServer()->broadcastMessage("[WP] $world is now OPEN");
				return true;
			case "lock":
				if (count($args)) return false;
				$this->owner->setCfg($world,"protect",$scmd);
				$this->owner->getServer()->broadcastMessage("[WP] $world is now LOCKED");
				return true;
			case "protect":
				if (count($args)) return false;
				$this->owner->setCfg($world,"protect",$scmd);
				$this->owner->getServer()->broadcastMessage("[WP] $world is now PROTECTED");
				return true;
		}
		return false;
	}

	protected function checkBlockPlaceBreak(Player $p) {
		$world = $p->getLevel()->getName();
		if (!isset($this->wcfg[$world])) return true;
		if ($this->wcfg["world"] != "protect") return false; // LOCKED!
		return $this->owner->canPlaceBreakBlock($p,$world);
	}

	public function onBlockBreak(BlockBreakEvent $ev){
		if ($ev->isCancelled()) return;
		$pl = $ev->getPlayer();
		if ($this->checkBlockPlaceBreak($pl)) return;
		$this->owner->msg($pl,"You are not allowed to do that here");
		$ev->setCancelled();
	}

	public function onBlockPlace(BlockPlaceEvent $ev){
		if ($ev->isCancelled()) return;
		$pl = $ev->getPlayer();
		if ($this->checkBlockPlaceBreak($pl)) return;
		$this->owner->msg($pl,"You are not allowed to do that here");
		$ev->setCancelled();
	}
}
