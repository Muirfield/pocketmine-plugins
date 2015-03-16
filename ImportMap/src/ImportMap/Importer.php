<?php
namespace ImportMap;
use \pocketmine\scheduler\AsyncTask;
use \pocketmine\Server;

class Importer extends AsyncTask {
  private $args;

  public function __construct($args) {
    $this->args = serialize($args);
  }
  public function onRun() {
    $this->setResult("ABORTED!");
    $args = unserialize($this->args);
    $start = time();
    $cmd = PHP_BINARY;
    // Configure PHAR file
    $args[0] = $args[0].'pmimporter.phar';
    foreach ($args as $v) {
      $cmd .= ' '.escapeshellarg($v);
    }
    $txt = shell_exec($cmd);
    $end = time();

    if ($start != $end) {
      $txt .= "\nRun-time: ".($end-$start)."\n";
    }
    $this->setResult($txt);
  }
  public function onCompletion(Server $server) {
    $server->broadcastMessage($this->getResult());
  }
}