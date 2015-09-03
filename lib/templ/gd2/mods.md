<?php
if (!isset($h)) $h = 4;
$mods = [];
$re = '/^module:/';
foreach (array_keys($snippets) as $a) {
  if (!preg_match($re,$a)) continue;
  $b = preg_replace($re,"",$a);
  $mods[$b] = $a;
}
ksort($mods);
foreach ($mods as $j=>$k) {
  echo str_repeat("#",$h)." ".$j."\n\n";
  echo implode("\n",$snippets[$k])."\n\n";
}
