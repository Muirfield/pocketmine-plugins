<?php
require_once(dirname(realpath(__FILE__))."/apitable.php");

function analyze_doc($otxt) {
	$txt = [""];
	$stateq = [ "body" ];

	foreach($otxt as $ln) {
		$x = strtolower(trim($ln));
    if (preg_match('/<!--\s*snippet:\s*(\S+)\s*-->/',$x,$mv)) {
			array_unshift($stateq,"find-eot");
			$txt[] = $ln;
			$txt[] = "\n<SNIPPET>\n".$mv[1];
			continue;
		}
		if (preg_match('/<!--\s*template:\s*(\S+)\s*-->/',$x,$mv)) {
			array_unshift($stateq,"find-eot");
			$txt[] = $ln;
			$txt[] = "\n<TEMPLATE>\n".$mv[1];
			continue;
		}
		if ($stateq[0] == "find-eot") {
			if (preg_match('/<!--\s*end-include\s*-->/',$x)) {
				$txt[] = $ln;
				array_shift($stateq);
			}
			continue;
		}
		if ($ln == "" && count($txt) > 1 && $txt[count($txt)-1] ==  "") continue;
		$txt[] = $ln;
	}
	return $txt;
}

function analyze_php($src, &$snippets) {
	$scode = basename($src);
	foreach(file($src,FILE_IGNORE_NEW_LINES) as $lni) {

		if (!preg_match('/^\s*\/\/(.) ?(.*)\s*$/',$lni,$mv)) {
			if (preg_match('/^(\s*)"#(.*)"\s*=>\s*"(.*)"\s*,?\s*$/',$lni,$mv) ||
				preg_match('/^(\s*)"#(.*)"\s*=>\s*"(.*)"\s*,?\s*\/\/\s*(.*)$/',$lni,$mv)) {

				// Probably a config doc line...
				if (count($mv) == 4) {
					list(,$indent,$setting,$descr) = $mv;
				}
				else {
					list(,$indent,$setting,$descr,$more) = $mv;
					$descr .= " ".$more;
				}
				if (!isset($snippets[$scode])) $snippets[$scode] = [];
				switch (substr($setting,0,1)) {
					case "|":
						$snippets[$scode][] = substr($setting,1).": ".$descr;
						break;
					default:
					  // Figure out indentation...
						$indent = "";
						for ($i = count($snippets[$scode])-1;$i >= 0;$i--) {
							if ($snippets[$scode][$i] == "") continue;
							if (preg_match('/^(\s*)/',$i,$mv)) $indent = $mv[1];
							break;
						}
						$snippets[$scode][] = $indent."* ".$setting.": ".$descr;
				}

			}
			continue;
		}
		list (,$sel,$ln) = $mv;

		if ($sel == ">") {
			$ln = htmlentities($ln);
		} elseif ($sel == "=") {
			$id = strtolower(trim($ln));
			if ($id == "") continue;
			$scode = $id;
			continue;
		} elseif ($sel == "#") {
			$id = strtolower(strtr(preg_replace('/^\s*[^a-zA-Z0-9]+\s*/',"",$ln),[" " =>""]));

			if ($id == "") continue;
			$scode = $id;
		} elseif ($sel != ":")
			continue;
		if (!isset($snippets[$scode])) $snippets[$scode] = [];
		$lc = count($snippets[$scode]);
		if ($ln == "" && $lc > 1 && $snippets[$scode][$lc-1] ==  "") continue;
		$snippets[$scode][] = $ln;
	}
}


function analyze_tree($dir) {
	$snippets = [];

	foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $s){
		if (!is_file($s)) continue;
		$d = substr($s,strlen($dir));
		if (!preg_match('/\.php$/',$d)) continue;
		$doc = analyze_php($s,$snippets);
	}
	return $snippets;
}

function startsWith($txt,$tok) {
	$ln = strlen($tok);
	if (substr($txt,0,$ln) != $tok) return null;
	return trim(substr($txt,$ln));
}

function expand_tags2($txt,&$snippets,&$yaml) {
	global $apitable;
	$meta = [];
	$out = [""];
	foreach ($txt as $ln) {
		if (($cm = startsWith($ln,"\n<SNIPPET>\n")) !== null) {
			if (isset($snippets[$cm])) {
				foreach ($snippets[$cm] as $i) {
					$out[] = $i;
				}
			} else {
				$out[] = "<!-- MISSING SNIPPET: $cm -->";
			}
		} elseif (($cm = startsWith($ln,"\n<TEMPLATE>\n")) !== null) {
			// Insert a template...
			ob_start();
			include(LIBDIR."templ/".$cm);
			foreach (explode("\n",ob_get_clean()) as $i) {
				$out[] = $i;
			}
		} else {
			if (isset($yaml["website"])) {
				$re = '/\[github\]\([^\)]+\)/';
				if (preg_match($re,$ln,$mv)) {
					$ln = preg_replace($re,'[github]('.$yaml["website"].')',$ln);
				}
			}

			$out[] = $ln;
			if (preg_match('/<!--\s*php:(.*)\s*-->/',$ln,$mv)) {
				eval($mv[1]);
			} elseif (preg_match('/<!--\s*meta:\s*(\S+)\s*[:=]\s*(.*)\s*-->/',$ln,$mv)) {
				$meta[$mv[1]] = $mv[2];
			}
		}
	}
	while (count($out) && $out[0] == "") array_shift($out);
	while (count($out)>1 && $out[count($out)-1] == "") array_pop($out);
	$out[] = "";
	$out[] = "";
	return $out;
}

//= syntax
//: # Embedded documentation syntax
//:
//: Embedded documentation is a "//" comment in PHP with the following text:
//:
//:     //=
//:
//: This introduces a new snippet definition.
//:
//:     //#
//:
//: This introduces a new snippet definition, but the text is also used
//:
//:     //:
//:
//: This adds body text to the snippet definition.
//:
//:     //>
//:
//: This adds body text but html entities are escaped.
//:
//: If the "=" or "#" tags are not used, the current file name is used.
//:
//# # GenDoc2 Generator
//:
//: Here we document GenDoc2
//:
//# ## Cmd:echo

function gendoc2($src, $readmelst, $yaml, $prefix = null) {
	if (!is_array($readmelst)) $readmelst = [ $readmelst ];
	if ($prefix === null) $prefix = SRCDIR;

  $snippets = analyze_tree($src);

	foreach ($readmelst as $readme) {
		if ($readme{0} != "/") $readme = SRCDIR.$readme;
		if (!file_exists($readme)) die("$readme: file not found\n");
		$otxt = file_get_contents($readme);
		$ntxt = analyze_doc(explode("\n",$otxt));
		$ntxt = expand_tags2($ntxt,$snippets,$yaml);
		$ntxt = implode("\n",$ntxt);
		if ($otxt != $ntxt) {
			file_put_contents($readme,$ntxt);
			echo "Updated ".basename($readme)."\n";
		}
	}

}
