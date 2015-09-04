<?php
if (!isset($h)) $h = 3;
$segments = [];
$re = '/^cmd:/';
foreach (array_keys($snippets) as $a) {
  if (!preg_match($re,$a)) continue;
  $b = preg_replace($re,"",$a);
  $j = explode(",",$b,2);
  if (count($j) == 1) $j[] = "Main";
  if (!isset($segments[$j[1]])) $segments[$j[1]] = [];
  $segments[$j[1]][$j[0]] = $a;
}

ksort($segments);
echo "\n";
foreach ($segments as $i=>&$j) {
  ksort($j);
  $n = ucwords(strtr($i,"_"," "));
  echo str_repeat("#",$h)." ".$n."\n\n";
  foreach ($j as $a=>$b) {
    echo "* ".$a.": ".$snippets[$b][0]."\n";
  }
  echo "\n";
}
