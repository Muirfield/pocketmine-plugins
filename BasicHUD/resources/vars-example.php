/*
 * This code is used to create new format variables
 *
 * The following variables are available:
 *
 * $plugin - the HUD plugin
 * $vars - array containing format variables
 * $player - current player
 */
$pm = $plugin->getServer()->getPluginManager();

if (($kr = $pm->getPlugin("KillRate")) !== null) {
	if (version_compare($kr->getDescription()->getVersion(),"1.1") >= 0) {
		$vars["{score}"] = $kr->getScore($player);
		$ranks = $kr->getRankings(3);
		if ($ranks == null) {
			$vars["{tops}"] = "N/A";
		} else {
			$vars["{tops}"] = "";
			$i = 1; $q = "";
			foreach ($ranks as $r) {
				$vars["{tops}"] .= $q.($i++).". ".substr($r["player"],0,8).
									 " ".$r["count"];
				$q = "   ";
			}
		}
	}
}
if (($mm = $pm->getPlugin("PocketMoney")) !== null) {
	$vars["{money}"] = $mm->getMoney($player->getName());
} elseif (($mm = $pm->getPlugin("MassiveEconomy")) !== null) {
	$vars["{money}"] = $mm->getMoney($player->getName());
} elseif (($mm = $pm->getPlugin("EconomyAPI")) !== null) {
	$vars["{money}"] = $mm->mymoney($player->getName());
} elseif (($mm = $pm->getPlugin("GoldStd")) !== null) {
	$vars["{money}"] = $mm->getMoney($player);
}
