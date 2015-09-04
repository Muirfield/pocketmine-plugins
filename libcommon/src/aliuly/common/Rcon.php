<?php
namespace aliuly\common;
//use pocketmine\scheduler\AsyncTask;
//use pocketmine\Server;
//use pocketmine\command\ConsoleCommandSender;

/**
 * This class implements a basic RCON client.
 *
 * It is done in a non Object Oriented fashion to make it easy to
 * use with an AsyncTask.
 */
abstract class Rcon {
	const RCTYPE_COMMAND = 2;
	const RCTYPE_AUTH = 3;

	/**
	 * Establishes a Rcon session.
	 *
	 * Returns an array with [$socket,$id].  In the event of an error
	 * returns a string with the error message.
	 *
	 * @param str $host - hostname or ip of remote server
	 * @param int $port - port to connect to
	 * @param str $auth - secret key
	 * @return str|array
	 */
  static public function connect($host,$port,$auth) {
		if (!($sock = @fsockopen($host,$port, $errno, $errstr, 30))) {
			return "Unable to open socket: $errstr ($errno)";
		}
		stream_set_timeout($sock,3,0);
		$id = 0;

		$pktid = self::writePkt(self::RCTYPE_AUTH,$auth,$sock,$id);
		$ret = [self::readPkt($sock)];
    if (is_null($ret)) return "Protocol error";
		if ($ret[0] == -1) return "Authentication failure";

		return [$sock,$id];
	}

	/**
	 * @param Rcon::RCTYPE_COMMAND|Rcon::RCTYPE_AUTH $type
	 * @param str $payload
	 * @param resource $sock
	 * @param &int id
	 * @return int
	 */
  static public function writePkt($type,$payload,$sock,&$id) {
    $myid = ++$id;
		$data = pack("VVV",strlen($payload),$myid,$type).
				($payload === "" ? "\x00" : $payload)."\x00";
		fwrite($sock,$data);
		return $myid;
	}
	/**
	 * @param resource $sock
	 * @return array|null
	 */
	static public function readPkt($sock) {
		$d = fread($sock, 4);
		if ($d === false || $d === ""  || strlen($d) < 4) return NULL;
		list($size) = array_values(unpack("V1",$d));
		if ($size < 0 or $size > 65535) return NULL;
		list($id) = array_values(unpack("V1", fread($sock, 4)));
		list($type) = array_values(unpack("V1", fread($sock, 4)));
		$payload = rtrim(fread($sock,$size-8));
		return [$id,$type,$payload];
	}
	/**
	 * Sends a remote command
	 *
	 * Returns [$results,$type] or a string with the error message
	 * @param str $cmd - command to execute
	 * @param resource $sock - connection from connect
	 * @param &int $id - id counter from connect
	 * @return  array|str
	 */
  static public function cmd($cmd,$sock,&$id) {
		$myid = self::writePkt(self::RCTYPE_COMMAND,$cmd,$sock,$id);
		$ret = self::readPkt($sock);
		if (is_null($ret)) return "Protocol error";
		list ($rid,$type,$payload) = $ret;
		if ($rid !== $myid) return "Sequencing error";
    return [$payload,$type];
	}
}
