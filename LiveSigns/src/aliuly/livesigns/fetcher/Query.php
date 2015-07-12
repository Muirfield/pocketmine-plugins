<?php
namespace aliuly\livesigns\fetcher;
use xPaw\MinecraftQuery;
use xPaw\MinecraftQueryException;


abstract class Query extends SignFetcher {
	static public function fetch($dat,$cfg) {
		if (!is_array($dat["content"])) {
			$f = $cfg["path"].$dat["content"];
			if (is_file($f)) {
				$txt = file($f,FILE_IGNORE_NEW_LINES);
				if ($txt === false) $txt = "Error reading file";
			} else {
				$txt = [$dat["content"]];
			}
		} else {
			$txt = $dat["content"];
		}

		$opts = explode(",",implode("\n",$txt),3);
		if (!isset($opts[0])) return "Query missing hostname";
		$host = $opts[0];
		$port = isset($opts[1]) ? $opts[1] : 19132; // Default port
		$msg = isset($opts[2]) ? $opts[2] : "{HostName}\n{Players}/{MaxPlayers}";
		$Query = new MinecraftQuery( );
		try {
			//echo __METHOD__.",".__LINE__."\n";//##DEBUG
			//echo "host=$host port=$port\n";//##DEBUG
			$Query->Connect( $host, $port, 1 );
		} catch (MinecraftQueryException $e) {
			return "Query ".$host." error: ".$e->getMessage();
		}
		$txt = [ $msg ];
		if (($info = $Query->GetInfo()) !== false) {
			foreach($info as $i=>$j) {
				if (is_array($j)) continue;
				$txt[] = $i."\t".$j;
			}
		}
		if (($players = $Query->GetPlayers()) !== false) {
			$list = "";
			foreach($players as $p) {
				$list .= $p."\n";
			}
			$txt[] = "PlayerList"."\t".$list;
		}		
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
		//print_r($vars);//##DEBUG
		return $txt;
	}
	static public function default_age() { return 8; }
}
