#!/usr/bin/php -n -d extension=posix.so
<?php
/**
 ** # RCON
 **
 ** * * *
 **
 **     Copyright (C) 2013 Alejandro Liu  
 **     All Rights Reserved.
 **
 **     This program is free software: you can redistribute it and/or modify
 **     it under the terms of the GNU General Public License as published by
 **     the Free Software Foundation, either version 2 of the License, or
 **     (at your option) any later version.
 **
 **     This program is distributed in the hope that it will be useful,
 **     but WITHOUT ANY WARRANTY; without even the implied warranty of
 **     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 **     GNU General Public License for more details.
 **
 **     You should have received a copy of the GNU General Public License
 **     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 **
 ** * * *
 **
 ** Simple RCON script
 **
 ** # Usage:
 **
 **
 **
 ** # Changes
 **
 ** * 0.1 : Initial release
 **
 ** # TODO
 **
 ** - write it
 **
 ** # Known Issues
 **
 ** - Doesnt work
 **
 **/
define("RCTYPE_COMMAND",2);
define("RCTYPE_AUTH",3);

class RCon {
  private $sock;
  private $id;

  public function __construct($host,$port) {
    $this->sock = @fsockopen($host,$port, $errno, $errstr, 30) or
      die("Unable to open socket: $errstr ($errno)\n");
    stream_set_timeout($this->sock,3,0);
    $this->id = 0;
  }
  function auth($passwd) {
    $pktid = $this->writePkt(RCTYPE_AUTH,$passwd);
    $ret = $this->readPkt();
    if (is_null($ret)) die("Protocol Error\n");
    if ($ret[0] == -1) die("Authentication failure\n");
  }
  private function writePkt($cmd,$payload = "") {
    $id = ++$this->id;
    $data = pack("VVV",strlen($payload),$id,$cmd).
      ($payload === "" ? "\x00" : $payload)."\x00";
    fwrite($this->sock,$data);
    return $id;
  }
  private function readPkt() {
    $d = fread($this->sock, 4);
    if ($d === false || $d === ""  || strlen($d) < 4) return NULL;
    list($size) = array_values(unpack("V1",$d));
    if ($size < 0 or $size > 65535) return NULL;
    list($id) = array_values(unpack("V1", fread($this->sock, 4)));
    list($type) = array_values(unpack("V1", fread($this->sock, 4)));
    $payload = rtrim(fread($this->sock,$size+2));
    return array($id,$type,$payload);
  }
  public function cmd($cmd) {
    $id = $this->writePkt(RCTYPE_COMMAND,$cmd);
    $ret = $this->readPkt();
    if (is_null($ret)) die("Protocol Error\n");
    list ($rid,$type,$payload) = $ret;
    if ($rid !== $id) die("Sequencing Error\n");
    return $payload;
  }
}

//////////////////////////////////////////////////////////////////////

function argchk($a,$b,&$c) {
  if (substr($a,0,strlen($b)) == $b) {
    $c = substr($a,strlen($b));
    return true;
  }
  return false;
}

function read_properties($f,&$s,&$pn,&$pw) {
  if (!is_file($f)) return;

  $lines = file($f,FILE_IGNORE_NEWLINES|FILE_SKIP_EMPTY_LINES);
  if ($lines === false) return;

  foreach ($lines as $ln) {
    list($k,$v) = preg_split('/\s*=\s*/',trim($ln),2);
    switch ($k) {
    case "server-ip":
      if ($v != "") $s = $v;
      break;
    case "rcon.port":
      $pn = $v;
      break;
    case "rcon.password":
      $pw = $v;
      break;
    }
  }
}

//////////////////////////////////////////////////////////////////////

$cmd = array_shift($argv);

$server = "127.0.0.1";
$port = 27015;
$passwd = NULL;

read_properties("server.properties",$server,$port,$passwd);

while (count($argv)) {
  if ($argv[0] == "-s") {
    array_shift($argv);
    $server = array_shift($argv);
  } elseif (argchk($argv[0],"--server=",$server)) {
    array_shift($argv);
  } elseif ($argv[0] == "-p") {
    array_shift($argv);
    $port = array_shift($argv);
  } elseif (argchk($argv[0],"--port=",$port)) {
    array_shift($argv);
  } elseif ($argv[0] == "-P") {
    array_shift($argv);
    $passwd = array_shift($argv);
  } elseif (argchk($argv[0],"--passwd=",$passwd)) {
    array_shift($argv);
  } elseif ($argv[0] == "--") {
    array_shift($argv);
    break;
  } else {
    break;
  }
}
if (is_null($passwd)) die("No password specified\n");


$rcon = new rcon($server,$port);
$rcon->auth($passwd);

if (count($argv)) {
  echo $rcon->cmd(implode(" ",$argv))."\n";
} else {
  if (posix_isatty(STDIN)) {
    echo "Enter /quit to finish\n";
    while (true) {
      $line = readline("RCON> ");
      if (trim($line) != "") {
	$t = trim($line);
	if ($line == "/quit") exit;
	readline_add_history($line);
	echo $rcon->cmd($line)."\n";
      }
    }
  } else {
    while (($line = stream_get_line(STDIN,1024,PHP_EOL)) !== false) {
      $line = trim($line);
      if ($line != "") {
	echo $rcon->cmd($line)."\n";
      }
    }
  }
}