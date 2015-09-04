<?php
$mods = [];
$re = '/^module:/';
foreach (array_keys($snippets) as $a) {
  if (!preg_match($re,$a)) continue;
  $b = preg_replace($re,"",$a);
  $mods[$b] = $a;
}
ksort($mods);
foreach ($mods as $j=>$k) {
  echo "* ".$j.": ".$snippets[$k][0]."\n";
}
