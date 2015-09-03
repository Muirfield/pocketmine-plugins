<?php
$cmds = [];
$re = '/^cmd:/';
foreach (array_keys($snippets) as $a) {
  if (!preg_match($re,$a)) continue;
  $b = preg_replace($re,"",$a);
  $cmds[$b] = $a;
}
ksort($cmds);
foreach ($cmds as $j=>$k) {
  $p = "* ".$j.": ";
  $q = "<br/>\n";
  foreach ($snippets[$k] as $i) {
    echo $p.$i.$q;
    $p = "  ";
    $q = "\n";
  }
}
