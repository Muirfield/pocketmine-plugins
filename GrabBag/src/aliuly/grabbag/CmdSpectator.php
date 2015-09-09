<?php
//= cmd:spectator|unspectator,Trolling
//: toggle a player's spectator mode **(DEPRECATED)**
//> usage: **spectator|unspectator** _[player]_
//:
//: This command will turn a player into an spectator.  In this mode
//: players can move but not interact (i.e. can't take/give damage,
//: can't place/break blocks, etc).
//:
//: If no player was specified, it will list spectators.


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

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\PermUtils;

class CmdSpectator extends BasicCli implements CommandExecutor,Listener {
	protected $watchers;

	public function __construct($owner) {
		parent::__construct($owner);
		PermUtils::add($this->owner, "gb.cmd.spectator", "Turn players into spectators", "op");
		$this->enableCmd("spectator",
							  ["description" => mc::_("Make player an spectator"),
								"usage" => mc::_("/spectator [player]"),
								"aliases"=> ["spc"],
								"permission" => "gb.cmd.spectator"]);
		$this->enableCmd("unspectator",
							  ["description" => mc::_("Reverses the effects of /spectator"),
								"usage" => mc::_("/unspectator [player]"),
								"aliases" => ["unspc"],
								"permission" => "gb.cmd.spectator"]);
		$this->watchers = [];
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		if (count($args) == 0) {
			$sender->sendMessage(mc::_("Spectators: %1%",count($this->watchers)));
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
						$player->sendMessage(mc::_("You are now an spectator"));
						$sender->sendMessage(mc::_("%1% is now an spectator",$n));
					} else {
						$sender->sendMessage(mc::_("%1% not found.",$n));
					}
				}
				return true;
			case "unspectator":
				foreach ($args as $n) {
					if (isset($this->watchers[strtolower($n)])) {
						unset($this->watchers[strtolower($n)]);
						$player = $this->owner->getServer()->getPlayer($n);
						if ($player) {
							$player->sendMessage(mc::_("You are no longer an spectator"));
						}
						$sender->sendMessage(mc::_("%1% is not an spectator",$n));
					} else {
						$sender->sendMessage(mc::_("%1% not found.",$n));
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
