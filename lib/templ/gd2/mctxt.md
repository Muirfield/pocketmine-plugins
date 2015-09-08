
## Translations

This plugin will honour the server language configuration.  The
languages currently available are:

* English
<?php
foreach (glob(SRCDIR."resources/messages/*.ini") as $f) { // x*
  $f = basename($f,".ini");
  if ($f == "messages" || $f == "eng") continue;
  switch ($f) {
    case "spa": $f = "Spanish"; break;
    case "deu": $f = "German"; break;
    case "nld": $f = "Dutch"; break;
    case "zho": $f = "中文"; break;
  }
  echo "* $f\n";
}
?>


You can provide your own message file by creating a file called
**messages.ini** in the plugin config directory.
<?php
if (isset($yaml["website"])) {
  echo "Check [github](".$yaml["website"]."/resources/messages/)\n";
  echo "for sample files.\n";
}
?>
Alternatively, if you have
[GrabBag](http://forums.pocketmine.net/plugins/grabbag.1060/)
installed, you can create an empty **messages.ini** using the command:

     pm dumpmsgs <?= $yaml["name"] ?> [lang]
