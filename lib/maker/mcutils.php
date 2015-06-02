<?php
namespace aliuly\common;

abstract class mcutils {
	public static function pofix($txt) {
		$txt = "\n".$txt."\n";
		return preg_replace('/\\\\n"\n"/',"\\n",
								  preg_replace('/\s+""\s*\n\s*"/'," \"",
													$txt));
	}
	public static function po2ini($src, $dst = null) {
		if ($dst != null) {
			$potxt = file_get_contents($src);
			$initxt = self::po2ini($potxt);
			if ($initxt === null) return false;
			return file_put_contents($initxt);
		}
		$c = preg_match_all('/\nmsgid "(.+)"\nmsgstr "(.*)"\n/',
								  self::pofix($src),$mm);
		if ($c == 0) return null;
		$dat = [];
		for($i=0;$i<$c;++$i) {
			$dat[$mm[1][$i]] = $mm[2][$i];
		}
		ksort($dat, SORT_NATURAL);
		$initxt = "; message file ".basename($dst)."\n";
		foreach ($dat as $a => $b) {
			$initxt .= "\"$a\"=\"$b\"\n";
		}
		return $initxt;
	}
	public static function ini2po($src, $dst = null) {
		if ($dst != null) {
			$initxt = file_get_contents($src);
			$potxt = self::ini2po($initxt);
			if ($potxt === null) return false;
			return file_put_contents($dst);
		}
		$c = preg_match_all('/^\s*"(.+)"\s*=\s*"(.*)"\s*$/m',
								  "\n".$src."\n",$mm);
		if ($c == 0) return null;
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
		return $potxt;
	}
}
