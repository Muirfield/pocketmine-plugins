<?php
// This template dumps permission stuff...
if (isset($yaml["permissions"])) {
  echo "\n### Permission Nodes\n\n";

  foreach ($yaml["permissions"] as $p => $pd) {
    $desc = isset($pd["description"]) ? $pd["description"] : $p;

    if (isset($pd["default"])) {
      $def = $pd["default"];
      if (is_bool($def)) {
        $def = $def ? "true" : "false";
      }
      switch (strtolower($def)) {
        case "true":
          $deftx = "";
          break;
        case "false":
          $deftx = " (disabled)";
          break;
        case "op":
        case "notop":
        default:
          $deftx = " (".$def.")";
      }
    } else {
      $deftx = " (op)";
    }
    echo "* $p$deftx: $desc\n";
  }
}
