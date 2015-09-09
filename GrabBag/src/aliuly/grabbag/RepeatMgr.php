<?php
//= module:repeater
//: Uses **!!** to repeat command with changes
//:
//: If you want to repeat a previous command enter **!!** *without* any "/"
//: in front.  This works for commands and chat messages.
//:
//: You can optionally append additional text to **!!** to do certain
//: things:
//:
//: * **!!** number
//:   - Will let you paginate output.  For example, entering:
//:     - /mw ls
//:     - !!2
//:     - !!3
//:   - This will start showing the output of **/mw ls** and consecutive pages.
//: * **!!** /
//:   - if you forgot the "/" in front, this command will add it.  Example:
//:     - help
//:     - !!/
//: * **!!** _text_
//:   - Will append _text_ to the previous command.  For example:
//:     - /gamemode
//:     - !! survival john
//:   - This will show the usage of survival, the next line will change the
//:     gamemode of john to survival.
//: * **!!** str1 str2
//:   - Will repeat the previous command replacing `str1` with `str2`
//:     Example:
//:     - /give player drt
//:     - !!drt dirt
//:   - This will change **drt** into **dirt**.
//: * **!!^** _text_
//:   - Will insert _text_ at the beginning of the command.
//:
namespace aliuly\grabbag;

use pocketmine\plugin\PluginBase as Plugin;
use pocketmine\event\Listener;
use pocketmine\Player;

use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\server\RemoteServerCommandEvent;
use pocketmine\event\server\ServerCommandEvent;

use aliuly\grabbag\common\mc;
use aliuly\grabbag\common\PermUtils;

class RepeatMgr implements Listener {
	public $owner;

	public function __construct(Plugin $plugin) {
		$this->owner = $plugin;
		PermUtils::add($this->owner, "gb.module.repeater", "use !! to repeat commands", "true");
		$this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
	}
	public function processCmd($msg,$sender) {
		if (preg_match('/^\s*!!/',$msg)) {
			$msg = trim(preg_replace('/^\s*!!\s*/','',$msg));
			// Match !
			$last = $this->owner->getState("RepeatMgr",$sender,false);

			if ($last === false) {
				$sender->sendMessage(mc::_("You do not have any recorded previous command"));
				return false;
			}
			// Just the previous command...
			if ($msg == "") return $last;
			if (is_numeric($msg)) {
				// We need to replace the last word with $msg....
				$words = preg_split('/\s+/',$last);
				if (count($words) == 1) {
					// Only a single world, we append the number...
					$newmsg = $last." ".$msg;
				} else {
					if (is_numeric($words[count($words)-1])) {
						// Exchange the last word (page count)
						$words[count($words)-1] = $msg;
						$newmsg = implode(" ",$words);
					} else {
						// Last word wasn't a number... append one
						$newmsg = $last." ".$msg;
					}
				}
			} elseif ($msg == "/" && substr($last,0,1) != "/") {
				// Forgotten "/"
				$newmsg = "/".$last;
			} elseif (substr($msg,0,1) == "^") {
				// Do we need space?
				if (preg_match('/^\s+/',$msg)) {
					$newmsg = trim(substr($msg,1))." ".$last;
				} else {
					$newmsg = trim(substr($msg,1)).$last;
				}
			} else {
				$words = preg_split('/\s+/',$msg,2);
				if (count($words) > 1
					 && stristr($last,$words[0]) !== false) {
					// Replace string
					$newmsg = str_ireplace($words[0],$words[1],$last);
				} else {
					// Add string...
					$newmsg = $last.' '.$msg;
				}
			}
			if (!($sender instanceof Player)) $sender->sendMessage(">> $newmsg");

			$last = $this->owner->setState("RepeatMgr",$sender,$newmsg);
			return $newmsg;
		}
		$last = $this->owner->setState("RepeatMgr",$sender,$msg);
		return false;
	}

	/**
	 * @priority LOWEST
	 */
	public function onPlayerCmd(PlayerCommandPreprocessEvent $ev) {
		if ($ev->isCancelled()) return;
		if (!$ev->getPlayer()->hasPermission("gb.module.repeater")) return;
		$res = $this->processCmd($ev->getMessage(),$ev->getPlayer());
		if ($res === false) return;
		$ev->setMessage($res);
	}
	/**
	 * @priority LOWEST
	 */
	public function onRconCmd(RemoteServerCommandEvent $ev) {
		$res = $this->processCmd($ev->getCommand(),$ev->getSender());
		if ($res === false) return;
		$ev->setCommand($res);
	}
	/**
	 * @priority LOWEST
	 */
	public function onConsoleCmd(ServerCommandEvent $ev) {
		$res = $this->processCmd($ev->getCommand(),$ev->getSender());
		if ($res === false) return;
		$ev->setCommand($res);
	}
}
