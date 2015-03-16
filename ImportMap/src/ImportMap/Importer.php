<?php
namespace ImportMap;
use \pocketmine\scheduler\AsyncTask;
use \pocketmine\Server;

class Importer extends AsyncTask {
  private $args;

  public static function phpRun(array $args) {
    $cmd = escapeshellarg(PHP_BINARY);
    foreach ($args as $i) {
      $cmd .= ' '.escapeshellarg($i);
    }
    //echo "CMD> $cmd\n";
    return shell_exec($cmd);
  }
  public function __construct($args) {
    $this->args = serialize($args);
  }
  public function onRun() {
    $this->setResult("ABORTED!");
    $args = unserialize($this->args);
    $start = time();
    $txt = self::phpRun($args);
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