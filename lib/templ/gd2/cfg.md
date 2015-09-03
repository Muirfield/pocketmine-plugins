<?php
if (!isset($h)) $h = 3;
$cfgs = [];
$re = '/^cfg:/';
foreach (array_keys($snippets) as $a) {
  if (!preg_match($re,$a)) continue;
  $b = preg_replace($re,"",$a);
  $cfgs[$b] = $a;
}
ksort($cfgs);
foreach ($cfgs as $j=>$k) {
  echo str_repeat("#",$h)." ".$j."\n\n";
  echo implode("\n",$snippets[$k])."\n\n";
}
