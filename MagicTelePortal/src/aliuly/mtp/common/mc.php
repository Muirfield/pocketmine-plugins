<?php
namespace aliuly\mtp\common;

abstract class mc {
	/*
	 * Use xgettext --no-wrap [-j] [-o file] to extract messages
	 * -j is to join existing message files
	 * -o output file
	 *
	 * Usage:
	 * mc::load("messages.po|messages.ini");
	 * mc::_("string to translate\n")
	 * mc::n(mc::_("Plural for one"),mc::_("Plural for not one"),$count)
	 */
	public static $txt = [];
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
	public static function plugin_init($plugin,$path) {
		if (file_exists($plugin->getDataFolder()."messages.ini")) {
			self::load($plugin->getDataFolder()."messages.ini");
			return;
		}
		$msgs = $path."resources/messages/".
				$plugin->getServer()->getProperty("settings.language").
				".ini";
		if (!file_exists($msgs)) return;
		mc::load($msgs);
	}

	public static function load($f) {
		$potxt = "\n".file_get_contents($f)."\n";
		if (preg_match('/\nmsgid\s/',$potxt)) {
			$potxt = preg_replace('/\\\\n"\n"/',"\\n",
										 preg_replace('/\s+""\s*\n\s*"/'," \"",
														  $potxt));
		}
		foreach (['/\nmsgid "(.+)"\nmsgstr "(.+)"\n/',
					 '/^\s*"(.+)"\s*=\s*"(.+)"\s*$/m'] as $re) {
			$c = preg_match_all($re,$potxt,$mm);
			if ($c) {
				for ($i=0;$i<$c;++$i) {
					if ($mm[2][$i] == "") continue;
					eval('$a = "'.$mm[1][$i].'";');
					eval('$b = "'.$mm[2][$i].'";');
					mc::$txt[$a] = $b;
				}
				return $c;
			}
		}
		return false;
	}
}
