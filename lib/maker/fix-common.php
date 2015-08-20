<?php
//
// Make common library local to the namespace...
//

function fix_file($php_old,$nspath,array &$commons) {
	$tr = [
		"namespace aliuly\\common;" => "namespace ".$nspath."\\common;",
		"use aliuly\\common\\" => "use ".$nspath."\\common\\",
	];
	if (preg_match_all('/use\s+aliuly\\\.*\\\common\\\/',$php_old,$mv)) {
		foreach ($mv as $n) {
			foreach ($n as $p) {
				$tr[$p] = "use ".$nspath."\\common\\";
			}
		}
	}
	$php_new = strtr($php_old,$tr);

	// Now look-up dependencies...
	if (preg_match_all('/use\s+aliuly\\\.*\\\common\\\(.*);/',$php_new,$mv)) {
		foreach ($mv[1] as $cn) {
			$commons[strtr($cn,["\\"=>DIRECTORY_SEPARATOR]).".php"] = $cn;
		}
	}

	return $php_new;


}



function fix_common($srcdir,$libdir,$plugin) {
	if (!is_dir($libdir."common")) {
		die("Missing common directory in $libdir\n");
	}
	if (!isset($plugin["main"]))
		die("Missing \"main\" declaration in plugin.yml\n");
	$path = explode("/",strtr($plugin["main"],"\\","/")); array_pop($path);
	$fpath = implode(DIRECTORY_SEPARATOR,$path);
	$nspath = implode("\\",$path);
	$commondir = $srcdir."src/".$fpath."/common/";
	if (!is_dir($commondir)) mkdir($commondir);
	$commons = [];


	foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcdir."src/".$fpath)) as $s){
		if (!is_file($s)) continue;
		if (!preg_match('/\.php$/',$s)) continue;
		if (basename(dirname($s)) == "common") continue;//Ignore files already in common...
		$php_in = file_get_contents($s);
		$php_out = fix_file($php_in,$nspath,$commons);
		if ($php_in != $php_out) {
			echo("Fixing ".substr($s,strlen($srcdir."src/"))."\n");
			file_put_contents($s,$php_out);
		}
		//echo "PROCESSING: $s\n";
	}
	// Handle dependencies
	$done = [];
	$queue = array_keys($commons);
	while (count($queue) > 0) {
		$cn = array_pop($queue);
		if (isset($done[$cn])) continue;
		$s = $libdir."common/".$cn;
		$d = $commondir.$cn;
		$php_in = file_get_contents($s);
		$mv = [];
		$php_out = fix_file($php_in,$nspath,$mv);
		if (!file_exists($d) || $php_out != file_get_contents($d)) {
			echo("Updating ".substr($d,strlen($srcdir."src/"))."\n");
			chkdir($d);
			file_put_contents($d,$php_out);
		}

		$done[$cn] = $cn;
		foreach ($mv as $j=>$k) {
			if (isset($done[$j])) continue;
			array_push($queue,$j);
			$commons[$j] = $k;
		}
	}
	// Clean-up unused files
	foreach (glob($commondir."*.php") as $php) {
		$b = basename($php);
		if (isset($commons[$b])) continue;
		echo "Deleting...".basename($php)."\n";
		unlink($php);
	}
}

function chkdir($p) {
	$d = dirname($p);
	if (is_dir($d)) return;
	chkdir($d);
	if (!mkdir($d)) die("Unable to create path: $d\n");
}
/*	  fix_file($s,$nspath,$commons);
	}
	}
	$done = [];
	$queue = array_keys($commons);
		foreach ($commons as $c) {
		}
	}

	if ($php_old != $php_new) {
		echo("Updating ".substr($phpd,strlen($srcdir."src/"))."\n");
		file_put_contents($phpd,$php_new);
	}

foreach (glob($srcdir."src/".$fpath."/*.php") as $php) {
		$tr2 = $tr;
	$php_old = file_get_contents($php);
		$php_new = strtr($php_old,$tr2);
	} else {
		$php_new = strtr($php_old,$tr);
	}
	if ($php_old != $php_new) {
	}
}
}*/
