<?php
//
// Get github download stats
//
function req($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	// Set so curl_exec returns the result instead of outputting it.
	curl_setopt($ch,CURLOPT_USERAGENT,"alejandroliu");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// Get the response and close the channel.
	$response = curl_exec($ch);
	curl_close($ch);
	return json_decode($response);
}

foreach (req("https://api.github.com/repos/Muirfield/pocketmine-plugins/releases")
			as $rel) {
	if (isset($rel->name) && $rel->name)
		echo "# ".$rel->name."\n";
	elseif (isset($rel->tag_name) && $rel->tag_name)
		echo "# ".$rel->tag_name."\n";
	else
		echo "# release\n";
	$tab = [];
	$cols = [1,1];
	if (isset($rel->assets)) {
		foreach ($rel->assets as $a) {
			if (isset($a->name) && isset($a->download_count)) {
				$tab[] = [ $a->name, $a->download_count ];
			}
		}
	}
	foreach ($tab as $row) {
		for ($i=0;$i<count($cols);$i++) {
			if (strlen($row[$i]) > $cols[$i]) $cols[$i] = strlen($row[$i]);
		}
	}
	foreach ($tab as $row) {
		printf("  - %-".$cols[0]."s %".$cols[1]."d\n",$row[0],$row[1]);
	}
}
