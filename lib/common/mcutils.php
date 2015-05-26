<?php
namespace aliuly\common;

abstract class mcutils {
	public static function pofix($txt) {
		return preg_replace('/\\\\n"\n"/',"\\n",
								  preg_replace('/\s+""\s*\n\s*"/'," \"",
													$txt));
	}
	public static function po2ini($src,$dst) {
		$potxt = "\n".file_get_contents($src)."\n";
		$c = preg_match_all('/\nmsgid "(.+)"\nmsgstr "(.*)"\n/',
								  self::pofix($potxt),$mm);
		if ($c == 0) return false;
		$dat = [];
		for($i=0;$i<$c;++$i) {
			$dat[$mm[1][$i]] = $mm[2][$i];
		}
		ksort($dat, SORT_NATURAL);
		$initxt = "; message file ".basename($dst)."\n";
		foreach ($dat as $a => $b) {
			$initxt .= "\"$a\"=\"$b\"\n";
		}
		return file_put_contents($dst,$initxt);
	}
	public static function ini2po($src,$dst) {
		$initxt = "\n".file_get_contents($src)."\n";
		$c = preg_match_all('/^\s*"(.+)"\s*=\s*"(.*)"\s*$/m',$initxt,$mm);
		if ($c == 0) return false;
		$dat = [];
		for($i=0;$i<$c;++$i) {
			$dat[$mm[1][$i]] = $mm[2][$i];
		}
		ksort($dat, SORT_NATURAL);
		$potxt = "# message file ".basename($dst)."\n".
				 "msgid \"\"\n".
				 "msgstr \"\"\n".
				 "\"Content-Type: text/plain; charset=utf-8\\n\"\n\n";

		foreach ($dat as $a => $b) {
			$potxt .= "#\nmsgid \"$a\"\nmsgstr \"$b\"\n\n";
		}
		return file_put_contents($dst,$potxt);
	}
}
