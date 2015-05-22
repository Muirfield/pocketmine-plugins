<?php
namespace aliuly\common;

class mc {
	/*
	 * Use xgettext [-j] to extract messages
	 * -j is to join existing message files
	 *
	 * Usage:
	 * mc::load("messages.po");
	 * mc::_("string to translate\n")
	 * mc::n(mc::_("Plural for one"),mc::_("Plural for not one"),$count)
	 */
	public static $txt;
	public static function _(...$args) {
		$fmt = array_shift($args);
		if (isset(self::$txt[$fmt])) $fmt = self::$txt[$fmt];
		if (count($args)) {
			$vars = [ "%%" => "%" ];
			$i = 1;
			foreach ($args as $j) {
				$vars["%$i%"] = $j;
				++$i;
			}
			$fmt = strtr($fmt,$vars);
		}
		return $fmt;
	}
	public static function n($a,$b,$c) {
		return $c == 1 ? $a : $b;
	}
	public static function load($f) {
		$potxt = file_get_contents($f);
		mc::$txt = [];
		$regex = '/\nmsgid "(.+?)"\nmsgstr "(.+?)"\n/';
		$c = preg_match_all($regex,$potxt,$mm);
		for ($i=0;$i<$c;++$i) {
			eval('$a = "'.$mm[1][$i].'";');
			eval('$b = "'.$mm[2][$i].'";');
			mc::$txt[$a] = $b;
		}
	}
}
