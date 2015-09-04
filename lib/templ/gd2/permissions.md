<?php
// This template dumps permission stuff...
if (isset($yaml["permissions"])) {
  echo "\n### Permission Nodes\n\n";

  foreach ($yaml["permissions"] as $p => $pd) {
    $desc = isset($pd["description"]) ? $pd["description"] : $p;
    echo "* $p : $desc\n";
    if (isset($pd["default"])) {
      if ($pd["default"] === "op") {
        echo "  (Defaults to Op)\n";
      } elseif (!$pd["default"]) {
        echo "  _(Defaults to disabled)_\n";
      }
    }
  }
}
