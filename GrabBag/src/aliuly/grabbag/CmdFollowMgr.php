<?php
/**
 ** OVERVIEW:Teleporting
 **
 ** COMMANDS
 **
 ** * followers : List who is following who
 **   usage: **followers**
 ** * follow : Follow a player
 **   usage: **follow** _<player>_
 ** * follow-off : stop following a player
 **   usage: **follow-off**
 ** * followme : Make a player follow you
 **   usage: **folowme** _<player>_
 ** * followme-off : stop making a player follow you
 **   usage: **followme-off** _<player>_
 **
 **/

namespace aliuly\grabbag;

use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;

use aliuly\grabbag\common\BasicCli;
use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\MPMU;

class CmdFollowMgr extends BasicCli implements Listener,CommandExecutor {
	protected $leaders;
	protected $followers;
	protected $maxdist = 8;

	public function __construct($owner) {
		parent::__construct($owner);
		$this->enableCmd("followers",
							  ["description" => mc::_("List leads and followers"),
								"usage" => mc::_("/followers"),
								"permission" => "gb.cmd.follow;gb.cmd.followme"]);
		$this->enableCmd("follow",
							  ["description" => mc::_("pursue a player"),
								"usage" => mc::_("/follow [player]"),
								"permission" => "gb.cmd.follow"]);
		$this->enableCmd("follow-off",
							  ["description" => mc::_("stop following a player"),
								"usage" => mc::_("/follow-off"),
								"permission" => "gb.cmd.follow"]);
		$this->enableCmd("followme",
							  ["description" => mc::_("drag player with you"),
								"usage" => mc::_("/followme [player]"),
								"permission" => "gb.cmd.followme"]);
		$this->enableCmd("followme-off",
							  ["description" => mc::_("stop dragging a player"),
								"usage" => mc::_("/followme-off [player]"),
								"permission" => "gb.cmd.followme"]);
		$this->leaders = [];
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	public function onCommand(CommandSender $sender,Command $cmd,$label, array $args) {
		$s = MPMU::iName($sender);
		switch ($cmd->getName()) {
			case "followers":
				$pageNumber = $this->getPageNumber($args);
				$txt = [ mc::_("Leads: %1%",count($this->leaders)) ];
				foreach ($this->leaders as $lead=>$followers) {
					$txt[]=$lead."(".count($followers)."):".implode(", ",$followers);
				}
				return $this->paginateText($sender,$pageNumber,$txt);
			case "follow":
				if (!MPMU::inGame($sender)) return true;
				if (count($args) != 1) return false;
				$n = array_shift($args);
				$player = $this->owner->getServer()->getPlayer($n);
				if (!$player) {
					$sender->sendMessage(mc::_("%1% not found.",$n));
					return true;
				}
				if (isset($this->followers[$s])) {
					$sender->sendMessage(mc::_("You are no longer following %1%",
														$this->followers[$s]));
					$this->stopFollowing($s);
				}
				$sender->sendMessage(mc::_("You are now following %1%",$n));
				$this->follow($s,$player);
				return true;
			case "follow-off":
				if (!MPMU::inGame($sender)) return true;
				if (count($args) != 0) return false;
				if (isset($this->followers[$s])) {
					$sender->sendMessage(mc::_("You are no longer following %1%",
														$this->followers[$s]));
					$this->stopFollowing($s);
				} else {
					$sender->sendMessage(mc::_("You are not following anybody"));
				}
				return true;
			case "followme":
				if (!MPMU::inGame($sender)) return true;
				if (count($args) == 0) return false;
				foreach ($args as $n) {
					$player = $this->owner->getServer()->getPlayer($n);
					if (!$player) {
						$sender->sendMessage(mc::_("%1% not found.",$n));
						continue;
					}
					$this->stopFollowing($player);
					$this->follow($player,$s);
					$sender->sendMessage(mc::_("%1% is now following you",$n));
					$player->sendMessage(mc::_("You are now following %1%",$s));
				}
				return true;
			case "followme-off":
				if (!MPMU::inGame($sender)) return true;
				if (count($args) != 0) return false;
				$this->stopLeading($s);
				$sender->sendMessage(mc::_("Nobody is following you"));
				return true;
		}
		return false;
	}
	private function follow($follower,$leader) {
		$follower = MPMU::iName($follower);
		$leader = MPMU::iName($leader);
		if (isset($this->followers[$follower])) $this->followStop($follower);
		if (!isset($this->leaders[$leader])) {
			// First follower!
			$this->leaders[$leader] = [];
		}
		$this->leaders[$leader][$follower] = $follower;
		$this->followers[$follower] = $leader;
		$this->approach($follower,$leader);
	}
	private function stopFollowing($follower) {
		$follower = MPMU::iName($follower);
		if (!isset($this->followers[$follower])) return;
		$leader = $this->followers[$follower];
		unset($this->followers[$follower]);
		if (!isset($this->leaders[$leader])) return;
		if (isset($this->leaders[$leader][$follower]))
			unset($this->leaders[$leader][$follower]);
		if (count($this->leaders[$leader]) == 0)
			unset($this->leaders[$leader]);
	}
	private function stopLeading($leader) {
		$leader = MPMU::iName($leader);
		if (!isset($this->leaders[$leader])) return;
		foreach ($this->leaders[$leader] as $follower) {
			if (isset($this->followers[$follower]))
				unset($this->followers[$follower]);
		}
		unset($this->leaders[$leader]);
	}
	private function approach($f,$l) {
		// "f=$f l=$l\n";//##DEBUG
		if (!($f instanceof Player)) {
			$f = $this->owner->getServer()->getPlayer($f);
			if (!$f) return; // Couldn't find this guy!
		}
		if (!($l instanceof Player)) {
			$l = $this->owner->getServer()->getPlayer($l);
			if (!$l) return; // Couldn't find this guy!
		}
		if (!$l->isonGround()) return; // We don't approach if leader is flying...

		if ($f->getLevel() === $l->getLevel()) {
			$dist = $f->distance($l);
			// $f->getName()." - ".$l->getName()." DIST:$dist\n";//##DEBUG
			if ($dist < $this->maxdist) return; // Close enough
		}
		$pos = $l->getLevel()->getSafeSpawn(new Vector3($l->getX()+mt_rand(-$this->maxdist,$this->maxdist),
								 $l->getY(),
								 $l->getZ()+mt_rand(-$this->maxdist,$this->maxdist)));
		$newdist = $pos->distance($l);
		if ($newdist > $this->maxdist) return;// Will not get close enough!
		$f->teleport($pos);
	}
	//
	// Event handlers
	//
	public function onPlayerQuit(PlayerQuitEvent $ev) {
		$this->stopFollowing($ev->getPlayer());
		$this->stopLeading($ev->getPlayer());
	}
	public function onPlayerMoveEvent(PlayerMoveEvent $ev) {
		$n = MPMU::iName($ev->getPlayer());
		if (isset($this->followers[$n]))
			$this->approach($n,$this->followers[$n]);
		if (isset($this->leaders[$n])) {
			foreach ($this->leaders[$n] as $follower) {
				$this->approach($follower,$n);
			}
		}
	}
}
