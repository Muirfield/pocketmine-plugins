<?php
function parse_readme($otxt) {
	$txt = [""];
	$state = "top";
	foreach($otxt as $ln) {
		$x = strtolower(trim($ln));
		if ($x == "## overview") {
			// Doing overview...
			$state = "overview";
			if ($txt[count($txt)-1] !=  "") $txt[] = "";
			$txt[] = $ln;
			continue;
		}
		if ($x == "## documentation") {
			$state = "doc";
			if ($txt[count($txt)-1] !=  "") $txt[] = "";
			$txt[] = $ln;
			continue;
		}
		if ($x == "### command reference") {
			$state = "cmdref";
			if ($txt[count($txt)-1] !=  "") $txt[] = "";
			$txt[] = $ln;
			$txt[] = "\n<CMDREF>";
			continue;
		}
		if ($x == "### module reference") {
			$state = "mgrs";
			if ($txt[count($txt)-1] !=  "") $txt[] = "";
			$txt[] = $ln;
			$txt[] = "\n<MGRREF>";
			continue;
		}
		if ($x == "### configuration") {
			$state = "config";
			if ($txt[count($txt)-1] !=  "") $txt[] = "";
			$txt[] = $ln;
			$txt[] = "\n<CONFIG>";
			continue;
		}
		if ($x == "### permission nodes") {
			$state = "perms";
			if ($txt[count($txt)-1] !=  "") $txt[] = "";
			$txt[] = $ln;
			$txt[] = "\n<PERMS>";
			continue;
		}
		if (substr($x,0,3) == "## ") {
			$state = "doc";
			if ($txt[count($txt)-1] !=  "") $txt[] = "";
			$txt[] = $ln;
			continue;
		}
		if (substr($x,0,2) == "# ") {
			$state = "top";
			if ($txt[count($txt)-1] !=  "") $txt[] = "";
			$txt[] = $ln;
			continue;
		}
		if ($state == "overview") {
			if (substr($x,0,4) == "### ") {
				$state = "ovw-sections";
				if ($txt[count($txt)-1] !=  "") $txt[] = "";
				$txt[] = "\n<OVW-SECTIONS>";
				continue;
			}
		}
		if (in_array($state,["ovw-sections","perms","cmdref","config","mgrs"]))
			continue;
		if ($ln == "" && $txt[count($txt)-1] ==  "") continue;

		$txt[] = $ln;
	}
	return $txt;
}

function analyze_src($src) {
	$doc = [];
	$state = "start";
	foreach(file($src,FILE_IGNORE_NEW_LINES) as $ln) {
		if($state == "start"){
			if (preg_match('/^\s*\/\*\*\s*$/',$ln)) {
				$state = "doc";
				continue;
			}
		}
		if($state == "doc" || substr($state,0,4) == "doc:") {
			if (preg_match('/^\s*\*\*\/\s*$/',$ln)) {
				break;
			}
			$ln  = preg_replace('/^\s*\*\* ?/',"",$ln,-1,$cnt);
			if ($cnt == 0) continue;
			$x = strtolower(trim($ln));
			if (substr($x,0,9) == "overview:") {
				$doc["overview"] = rtrim(preg_replace('/^\s*overview:\s*/i',"",$ln));
				continue;
			}
			if (substr($x,0,7) == "module:") {
				$state = "doc:mgr";
				$doc["mgr"] = [];
				$doc["mgr"][] = rtrim(preg_replace('/^\s*module:\s*/i',"",$ln));
				continue;
			}
			if ($x == "commands") {
				$state = "doc:cmds:";
				$doc["commands"] = [];
				continue;
			}
			if (substr($x,0,7) == "config:") {
				$doc["config"] = [];
				$doc["config"][] = rtrim(preg_replace('/^\s*config:\s*/i',"",$ln));
				$state = "doc:config";
				continue;
			}
			if ($x == "docs") {
				$state = "doc:docs";
				$doc["docs"] = [];
				continue;
			}

			if (substr($state,0,9) == "doc:cmds:") {
				if (preg_match('/^\s*\*\s+([^\s]+)\s*:\s*/',$ln,$mv)) {
					$cmd = $mv[1];
					$desc = substr($ln,strlen($mv[0]));
					$state = "doc:cmds:".$cmd;
					$doc["commands"][$cmd] = [
						"cmd" => $cmd,
						"sdesc" => $desc,
						"desc" => [],
					];
					continue;
				}
				$cmd = substr($state,9);
				if ($cmd == "") continue;
				if (!isset($doc["commands"][$cmd])) continue;
				if (preg_match('/^\s*usage\s*:\s*/',$ln,$mv)) {
					$doc["commands"][$cmd]["usage"] = substr($ln,strlen($mv[0]));
					continue;
				}
				$doc["commands"][$cmd]["desc"][] = $ln;
				continue;
			}
			if ($state == "doc:config") {
				$doc["config"][] = $ln;
				continue;
			}
			if ($state == "doc:docs") {
				$doc["docs"][] = $ln;
				continue;
			}
			if ($state == "doc:mgr") {
				$doc["mgr"][] = $ln;
				continue;
			}
		}
	}
	return $doc;
}

function analyze_tree($dir) {
	$db = [
		"cmds" => [],
		"config" => [],
		"perms" => [],
		"overview" => [],
		"docs" => [],
		"mgrs" => [],
	];

	foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $s){
		if (!is_file($s)) continue;
		$d = substr($s,strlen($dir));
		if (!preg_match('/\.php$/',$d)) continue;
		$doc = analyze_src($s);
		if (isset($doc["config"])) {
			$cfgname = array_shift($doc["config"]);
			$db["config"][$cfgname] = $doc["config"];
		}
		if (isset($doc["mgr"])) {
			$name = array_shift($doc["mgr"]);
			while ($doc["mgr"][0] == "") array_shift($doc["mgr"]);
			$db["mgrs"][$name] = $doc["mgr"];
		}
		if (isset($doc["overview"])) {
			if (!isset($db["overview"][$doc["overview"]])) {
				$db["overview"][$doc["overview"]] = [];
			}
			if (isset($doc["commands"])) {
				foreach ($doc["commands"] as $i=>$j) {
					if (isset($j["sdesc"])) {
						$db["overview"][$doc["overview"]][$i] = $j["sdesc"];
					}
				}
			}
		}
		if (isset($doc["commands"])) {
			foreach ($doc["commands"] as $i=>$j) {
				$db["cmds"][$i] = $j;
			}
		}
		if (isset($doc["docs"])) {
			foreach ($doc["docs"] as $ln) {
				$db["docs"][] = $ln;
			}
			if (count($db["docs"]) > 1 && $db["docs"][count($db["docs"])-1] != "") {
				$db["docs"][] = "";
			}
		}
	}
	return $db;
}

function expand_tags($txt,$db,$yaml) {
	$out = [""];
	foreach ($txt as $ln) {
		switch ($ln) {
			case "\n<PERMS>":
				if (isset($yaml["permissions"])) {
					if ($out[count($out)-1] !=  "") $out[] = "";
					foreach ($yaml["permissions"] as $p => $pd) {
						$desc = isset($pd["description"]) ? $pd["description"] : $p;
						$out[] = "* $p : $desc";
						if (isset($pd["default"])) {
							if ($pd["default"] === "op") {
								$out[] = "  (Defaults to Op)";
							} elseif (!$pd["default"]) {
								$out[] = "  _(Defaults to disabled)_";
							}
						}
					}
					$out[] = "";
				}
				break;
			case "\n<MGRREF>":
				if (isset($db["mgrs"]) && count($db["mgrs"])) {
					if ($out[count($out)-1] !=  "") $out[] = "";
					ksort($db["mgrs"],SORT_NATURAL|SORT_FLAG_CASE);
					foreach ($db["mgrs"] as $i => $j) {
						$out[] = "#### $i";
						$out[] = "";
						foreach ($j as $x) {
							$out[] = $x;
						}
						if ($out[count($out)-1] !=  "") $out[] = "";
					}
				}
				break;
				break;
			case "\n<CMDREF>":
				if ($out[count($out)-1] !=  "") $out[] = "";
				$out[] = "The following commands are available:";
				$out[] = "";
				ksort($db["cmds"],SORT_NATURAL|SORT_FLAG_CASE);
				foreach ($db["cmds"] as $cm=>$cc) {
					$sdesc = isset($cc["sdesc"]) ? $cc["sdesc"] : "";
					$usage = isset($cc["usage"]) ? $cc["usage"] : "";
					if ($usage != "") {
						$out[] = "* ".htmlentities($usage)."  ";
						if ($sdesc) $out[] = "  $sdesc  ";
					} elseif ($sdesc != "") {
						$out[] = "* $cm : $sdesc  ";
					} else {
						$out[] = "* $cm  ";
					}
					if (isset($cc["desc"])) {
						foreach ($cc["desc"] as $x) {
							$out[] = $x;
						}
					}
				}
				if ($out[count($out)-1] !=  "") $out[] = "";
				foreach ($db["docs"] as $z) {
					$out[] = $z;
				}
				if ($out[count($out)-1] !=  "") $out[] = "";
				break;
			case "\n<CONFIG>":
				if ($out[count($out)-1] !=  "") $out[] = "";
				$out[] = "Configuration is throug the `config.yml` file.";
				$out[] = "The following sections are defined:";
				$out[] = "";
				ksort($db["config"],SORT_NATURAL|SORT_FLAG_CASE);
				foreach ($db["config"] as $s => $l) {
					$out[] = "#### $s";
					$out[] = "";
					foreach ($l as $xz) {
						$out[] = $xz;
					}
					if ($out[count($out)-1] !=  "") $out[] = "";
				}
				break;
			case "\n<OVW-SECTIONS>":
				ksort($db["overview"],SORT_NATURAL|SORT_FLAG_CASE);
				foreach ($db["overview"] as $sect => $cmds) {
					if ($out[count($out)-1] !=  "") $out[] = "";
					$out[] = "### $sect";
					$out[] = "";
					ksort($cmds,SORT_NATURAL|SORT_FLAG_CASE);
					foreach($cmds as $n=>$d) {
						$out[] = "* $n : $d";
					}
				}
				if (isset($db["mgrs"]) && count($db["mgrs"])) {
					if ($out[count($out)-1] !=  "") $out[] = "";
					$out[] = "### Modules";
					$out[] = "";
					ksort($db["mgrs"],SORT_NATURAL|SORT_FLAG_CASE);
					foreach ($db["mgrs"] as $i => $j) {
						if (count($j)) {
							$out[] = "* $i : $j[0]";
						} else {
							$out[] = "* $i";
						}
					}
				}
				break;
			default:
				$out[] = $ln;
		}
	}
	while (count($out) && $out[0] == "") array_shift($out);
	while (count($out)>1 && $out[count($out)-1] == "") array_pop($out);
	$out[] = "";
	$out[] = "";
	return $out;
}

function gendoc($readme,$yaml) {
	if (!file_exists($readme)) die("$readme: file not found\n");
	$otxt = file_get_contents($readme);
	$txt = parse_readme(explode("\n",$otxt));
	$db = analyze_tree(dirname($readme));
	$out = expand_tags($txt,$db,$yaml);
	$ntxt = implode("\n",$out);
	if ($otxt != $ntxt) {
		file_put_contents($readme,implode("\n",$out));
		echo "Updated ".basename($readme)."\n";
	}
}
