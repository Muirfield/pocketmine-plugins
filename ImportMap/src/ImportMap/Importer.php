<?php
namespace ImportMap;
use \pocketmine\scheduler\AsyncTask;
use \pocketmine\Server;

class Importer extends AsyncTask {
  private $args;

  private static function makeCmdLine(array &$args) {
    $cmd = escapeshellarg(PHP_BINARY);
    foreach ($args as $i) {
      $cmd .= ' '.escapeshellarg($i);
    }
    return $cmd;
  }

  public static function phpRun(array $args) {
    return shell_exec(self::makeCmdLine($args));
  }
  public function __construct($args) {
    $this->args = serialize($args);
  }
  public function onRun() {
    $this->setResult("ABORTED!");
    $args = unserialize($this->args);
    $cmd = self::makeCmdLine($args);
    echo "CMD> $cmd\n";
    $start = time();
    $fp = popen($cmd,"r");
    $txt = '';
    while (($c = fread($fp,64)) != false) {
      echo($c);
      $txt.=$c;
    }
    $end = time();
    if ($end - $start > 15) {
      $txt .= "\nRun-time: ".($end-$start);
    }
    $this->setResult($txt);
  }
  public function onCompletion(Server $server) {
    $server->broadcastMessage($this->getResult());
  }
}