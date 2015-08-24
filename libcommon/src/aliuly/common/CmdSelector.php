<?php
namespace aliuly\common;

use aliuly\grabbag\common\mc;

use aliuly\common\selectors\All;
use aliuly\common\selectors\AllEntity;
use aliuly\common\selectors\Random;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\entity\Entity;
use pocketmine\command\CommandSender;

abstract class CmdSelector {
  /** @var int - max number of commands to expand to... */
  static public $max = 100;
  /**
   * Expand command selectors
   * @param Server $server - Server context
   * @param CommandSender $sender - context executing this command
   * @param str $cmdline - command line to expand
   * @return str[]
   */
  static public function expandSelectors(Server $server, CommandSender $sender, $cmdline) {
		$tokens = preg_split('/\s+/',$cmdline);

		$res = [ $tokens ];
		$ret = false;

		foreach ($tokens as $argc=>$argv){
			if (!$argc) continue; // Trivial case...
			if (substr($argv,0,1) !== "@" ) continue;

			$selector = substr($argv, 1);
			$sargs = [];
			if(($i = strpos($selector, "[")) !== false){
				foreach (explode(",",substr($selector,$i+1,-1)) as $kv) {
					$kvp = explode("=",$kv,2);
					if (count($kvp) != 2) {
						$sender->sendMessage(mc::_("Selector: invalid argument %1%",$kv));
						continue;
					}
					$sargs[$kvp[0]] = strtolower($kvp[1]);
				}
				$selector = substr($selector,0,$i);
				print_r($sargs);//##DEBUG
			}
			$results = self::dispatchSelector($server, $sender , $selector, $sargs);
			if (!is_array($results)) continue;
			$ret = true;
			$new = [];

			foreach ($res as $i) {
				foreach ($results as $j) {
					$tmpLine = $i;
					$tmpLine[$argc] = $j;
					$new[] = $tmpLine;
					if (count($new) > self::$max) break;
				}
				if (count($new) > self::$max) break;
			}
			$res = $new;
		}
		if (!$ret) return false;
		$new = [];
		foreach ($res as $i) {
			$new[] = implode(" ",$i);
		}
		return $new;
	}

  static protected function dispatchSelector(Server $server, CommandSender $sender,$selector,array $args) {
		switch ($selector) {
			case "a":
			  return All::select($server, $sender , $args);
			case "e":
				return AllEntity::select($server, $sender, $args);
			case "r":
			  return Random::select($server, $sender, $args);
		  //case "p":
		}
		return null;
	}

}
