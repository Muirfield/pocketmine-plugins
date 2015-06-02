<?php
//
// Make common library local to the namespace...
//
function fix_common($srcdir,$libdir,$plugin) {
	if (!isset($plugin["main"]))
		die("Missing \"main\" declaration in plugin.yml\n");
	$path = explode("/",strtr($plugin["main"],"\\","/")); array_pop($path);
	$fpath = implode(DIRECTORY_SEPARATOR,$path);
	$nspath = implode("\\",$path);
	if (!is_dir($srcdir."src/".$fpath."/common")) {
		echo "No common path in $fpath.\n";
		return;
	}
	if (!is_dir($libdir."common")) {
		die("Missing common directory in $libdir\n");
	}
	$tr = [
		"namespace aliuly\\common;" => "namespace ".$nspath."\\common;",
		"use aliuly\\common\\" => "use ".$nspath."\\common\\",
	];
	foreach (glob($srcdir."src/".$fpath."/common/*.php") as $phpd) {
		$phps = $libdir."common/".basename($phpd);
		if (!is_file($phps)) {
			echo("$phps: not found!\n");
			continue;
		}
		$php_old = file_get_contents($phpd);
		$php_new = strtr(file_get_contents($phps),$tr);
		if ($php_old != $php_new) {
			echo("Updating $phpd\n");
			file_put_contents($phpd,$php_new);
		}
	}
	$tr = [
		"use aliuly\\common\\" => "use ".$nspath."\\common\\",
	];
	foreach (glob($srcdir."src/".$fpath."/*.php") as $php) {
		$php_old = file_get_contents($php);
		$php_new = strtr($php_old,$tr);
		if ($php_old != $php_new) {
			echo("Fixing $php\n");
			file_put_contents($php,$php_new);
		}
	}
}
