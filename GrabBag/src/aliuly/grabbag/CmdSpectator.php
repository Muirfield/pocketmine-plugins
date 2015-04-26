<?php
/**
 ** OVERVIEW:Trolling
 **
 ** COMMANDS
 **
 ** * spectator|unspectator : toggle a player's spectator mode
 **   usage: **spectator|unspectator** _[player]_
 **
 **   `/spectator` will turn a player into an spectator.  In this mode
 **   players can move but not interact (i.e. can't take/give damage,
 **   can't place/break blocks, etc).
 **
 **   If no player was specified, it will list spectators.
 **/

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\Player;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerInteractEvent;

class CmdSpectator extends BaseCommand implements Listener {
	protected $watchers;

	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("spectator",
							  ["description" => "Make player an spectator",
								"usage" => "/spectator [player]",
								"aliases"=> ["spc"],
								"permission" => "gb.cmd.spectator"]);
		$this->enableCmd("unspectator",
							  ["description" => "Reverses the effects of /spectator",
								"usage" => "/unspectator [player]",
								"aliases" => ["unspc"],
								"permission" => "gb.cmd.spectator"]);
		$this->watchers = [];
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if (count($args) == 0) {
			$sender->sendMessage("Spectators: ".count($this->watchers));
			if (count($this->watchers))
				$sender->sendMessage(implode(", ",$this->watchers));
			return true;
		}
		switch ($cmd->getName()) {
			case "spectator":
				foreach ($args as $n) {
					$player = $this->owner->getServer()->getPlayer($n);
					if ($player) {
						$this->watchers[strtolower($player->getName())] = $player->getName();
						$player->sendMessage("You are now an spectator");
						$sender->sendMessage("$n is now an spectator");
					} else {
						$sender->sendMessage("$n not found.");
					}
				}
				return true;
			case "unspectator":
				foreach ($args as $n) {
					if (isset($this->watchers[strtolower($n)])) {
						unset($this->watchers[strtolower($n)]);
						$player = $this->owner->getServer()->getPlayer($n);
						if ($player) {
							$player->sendMessage("You are no longer an spectator");
						}
						$sender->sendMessage("$n is not an spectator");
					} else {
						$sender->sendMessage("$n not found");
					}
				}
				return true;
		}
		return false;
	}
	public function onDamage(EntityDamageEvent $ev) {
		if ($ev->isCancelled()) return;
		if ($ev->getEntity() instanceof Player) {
			if (isset($this->watchers[strtolower($ev->getEntity()->getName())])) {
				$ev->setCancelled();
				return;
			}
		}
		if($ev instanceof EntityDamageByEntityEvent) {
			if ($ev->getDamager() instanceof Player) {
				if (isset($this->watchers[strtolower($ev->getDamager()->getName())])) {
					$ev->setCancelled();
					return;
				}
			}
		}
	}
	public function onBlockBreak(BlockBreakEvent $ev) {
		if ($ev->isCancelled()) return;
		if (isset($this->watchers[strtolower($ev->getPlayer()->getName())])) {
			$ev->setCancelled();
		}
	}
	public function onBlockPlace(BlockPlaceEvent $ev) {
		if ($ev->isCancelled()) return;
		if (isset($this->watchers[strtolower($ev->getPlayer()->getName())])) {
			$ev->setCancelled();
		}
	}
	public function onInteract(PlayerInteractEvent $ev) {
		if ($ev->isCancelled()) return;
		if (isset($this->watchers[strtolower($ev->getPlayer()->getName())])) {
			$ev->setCancelled();
		}
	}
}
