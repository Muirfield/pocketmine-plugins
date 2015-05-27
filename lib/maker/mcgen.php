<?php
//
// Generate message catalogues
//
require_once(LIBDIR."common/mcutils.php");
use aliuly\common\mcutils;

function xgettext_r($po,$srcdir) {
	foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcdir)) as $s){
		if (!is_file($s)) continue;
		if (!preg_match('/\.php$/',$s)) continue;
		$cmd = 'xgettext --no-wrap -o '.$po;

		if (file_exists($po)) {
			$cmd .= ' -j';
			$new = false;
		} else {
			$new = true;
		}
		$cmd .= ' '.$s;
		//echo ($cmd."\n");
		system($cmd);
		// Make sure the CHARSET is defined properly...
		if ($new) {
			$potxt = file_get_contents($po);
			if (preg_match('/Content-Type:\s+text\/plain;\s+charset=CHARSET/',
								$potxt)) {
				file_put_contents($po,preg_replace('/\s+charset=CHARSET/',
															  ' charset=utf-8',$potxt));
			}
			unset($potxt);
		}
	}
}

function mcgen($mcdir,$srcdir) {
	if (!is_dir($srcdir)) die("$srcdir: Source not found\n");
	if (!is_dir($mcdir)) return;

	$templ = "$mcdir/messages.ini";
	if (!file_exists($templ)) file_put_contents($templ,"");

	foreach (glob("$mcdir/*.ini") as $mc) {
		$po = preg_replace('/\.ini$/','.po',$mc);
		$intxt = file_get_contents($mc);
		if ($mc == $templ) {
			if (file_exists($po)) unlink($po);
		} else {
			$potxt = mcutils::ini2po($intxt);
			if ($potxt === null) {
				if (file_exists($po)) unlink($po);
			} else {
				file_put_contents($po,$potxt);
			}
		}

		xgettext_r($po,$srcdir);
		if (!file_exists($po)) {
			echo ("xgettext_r error\n");
			return;
		}
		$potxt = file_get_contents($po);
		$outtxt = mcutils::po2ini($potxt);
		if ($outtxt === null) {
			echo ("Error updating ".basename($mc)."\n");
			return;
		}
		unlink($po);
		if ($intxt != $outtxt) {
			file_put_contents($mc,$outtxt);
			echo "Updated ".basename($mc)."\n";
		}
	}
}
