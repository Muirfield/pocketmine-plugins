<?php
use pocketmine\utils\TextFormat;

$pm = $plugin->getServer()->getPluginManager();
if (($kr = $pm->getPlugin("KillRate")) !== null) {
	if (version_compare($kr->getDescription()->getVersion(),"1.1") >= 0) {
		$i = 1;
		$ranks = $kr->getRankings(3);
		if ($ranks == null) {
			echo TextFormat::BLUE."No rankings available\n";
		} else {
			$i = 1;
			echo "KillRate rankings\n";
			foreach ($ranks as $r) {
				echo TextFormat::AQUA.$i++.". ".$r["player"]." ".$r["count"]."\n";
			}
		}
	} else {
		echo TextFormat::YELLOW."Please upgrade your version of KillRate\n";
	}
} else {
	echo TextFormat::RED."Please install the KillRate plugin\n";
}
