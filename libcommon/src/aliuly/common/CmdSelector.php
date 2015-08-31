<?php
namespace aliuly\common;

use aliuly\common\mc;

use aliuly\common\selectors\BaseSelector;
use aliuly\common\selectors\All;
use aliuly\common\selectors\AllEntity;
use aliuly\common\selectors\Random;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\entity\Entity;
use pocketmine\command\CommandSender;

abstract class CmdSelector {
  /**
   * Expand command selectors.
   * Returns an array with string substitutions or `false` if no expansions
   * occurred.
   *
   * @param Server $server - Server context
   * @param CommandSender|null $sender - context executing this command
   * @param str $cmdline - command line to expand
   * @param int $max - max number of expansions
   * @return str[]|false
   */
  static public function expandSelectors(Server $server, $sender, $cmdline, $max= 100) {
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
						if ($sender instanceof CommandSender) $sender->sendMessage(mc::_("Selector: invalid argument %1%",$kv));
						continue;
					}
					$sargs[$kvp[0]] = strtolower($kvp[1]);
				}
				$selector = substr($selector,0,$i);
			}
			$results = self::dispatchSelector($server, $sender , $selector, $sargs);
			if (!is_array($results) || count($results) == 0) continue;
			$ret = true;
			$new = [];

			foreach ($res as $i) {
				foreach ($results as $j) {
					$tmpLine = $i;
					$tmpLine[$argc] = $j;
					$new[] = $tmpLine;
					if (count($new) > $max) break;
				}
				if (count($new) > $max) break;
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
