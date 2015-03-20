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
    while (($c = fread($fp,64)) != false) {
      echo($c);
    }
    pclose($fp);
    $end = time();
    $this->setResult("Run-time: ".($end-$start));
  }
  public function onCompletion(Server $server) {
    $server->broadcastMessage($this->getResult());
  }
}