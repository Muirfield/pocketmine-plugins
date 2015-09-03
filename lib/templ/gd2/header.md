<?php
  list($v,$m) = [[],[]];

  foreach (["name","description","depend","softdepend","api","website"] as $attr) {
    if (!isset($yaml[$attr])) {
      $v[$attr] = "\n";
      continue;
    }
    if ($attr == "api") {
      $items = [];
      $api = is_array($yaml[$attr]) ? $yaml[$attr] : [$yaml[$attr]];
      foreach ($api as $j) {
        if (isset($apitable[$j]))
          $items[] = $apitable[$j];
        else
          $items[] = $j;
      }
      $v[$attr] = implode(", ", $items)."\n";
      continue;
    }
    if (is_array($yaml[$attr]))
      $v[$attr] = implode(", ", $yaml[$attr])."\n";
    else {
      $v[$attr] = $yaml[$attr]."\n";
    }
  }
  foreach (["Categories","PluginAccess"] as $attr) {
    $m[$attr] = (isset($meta[$attr]) ? $meta[$attr] : "N/A")."\n";
  }
?>

# <?= $v["name"] ?>

- Summary: <?= $v["description"] ?>
- PocketMine-MP version: <?= $v["api"] ?>
- DependencyPlugins: <?= $v["depend"] ?>
- OptionalPlugins: <?= $v["softdepend"] ?>
- Categories: <?= $m["Categories"] ?>
- Plugin Access: <?= $m["PluginAccess"] ?>
- WebSite: <?= $v["website"] ?>
