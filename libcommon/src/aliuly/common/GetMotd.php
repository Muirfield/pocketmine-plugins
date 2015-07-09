<?php

namespace aliuly\common;
use pocketmine\utils\Binary;

/**
 * Get MOTD style data from Minecraft PE servers
 */
abstract class GetMotd {
  /** @const str - Magic string */
  const MAGIC = "\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78";
  /** @const int - Ping Open connection packet id */
	const PING_OPEN_CONNECTION = 0x01;
  /** @const int - Pong Open connection packet id */
	const PONG_OPEN_CONNECTION = 0x1c;
	/** Query server
	 * @param str $Ip - IP or hostname to query
	 * @param int $Port - Port to connect to
	 * @param int $Timeout - Timeout in seconds
	 * @return str|array - string with error on array with results
	 */
	static public function query( $Ip, $Port = 19132, $Timeout = 3 ) {
		if( !is_int( $Timeout ) || $Timeout < 0 )	return "Invalid timeout value";
		$sock = @fsockopen( 'udp://' . $Ip, (int)$Port, $ErrNo, $ErrStr, $Timeout );

		if( $ErrNo || $sock === false ) return "socket error: " . $ErrStr;
		Stream_Set_Timeout( $sock, $Timeout );
		Stream_Set_Blocking( $sock, true );

		$res = self::pingServer($sock);
		fclose($sock);
		return $res;
	}
  /**
   * Run protocol to get MOTD data
   *
   * @param resource $sock
   * @return str|array - string with error or array with results
   */
	static protected function pingServer($sock) {
		$pkt = chr(self::PING_OPEN_CONNECTION).
					Binary::writeLong(microtime(true)*1000).
					self::MAGIC;
		$len  = strlen($pkt);
		if ($len !== fwrite($sock,$pkt,$len)) return "error writing socket";
		$reply = fread( $sock, 4096 );
		if ($reply === false) return "error reading socket";
		/*
			0 - id
			1 - ping-id
			9 - server-id
			17 - magic
			33 - string Length
			35 - payload Length
			37 - payload string
			*/
		if (strlen($reply) < 35
				|| $reply{0} != chr(self::PONG_OPEN_CONNECTION)
				|| substr($reply,17,16) != self::MAGIC) return "invalid response";
		$res = [
			"latency" => intval(microtime(true)*1000 - Binary::readLong(substr($reply,1,8))),
			"serverId" => Binary::readLong(substr($reply,9,8)),
		];
		$plen = Binary::readShort(substr($reply,35,2));
		$payload = substr($reply,37);
		if (strlen($payload) > $plen) $payload = substr($payload,$plen);
		$val = explode(";",$payload);
    foreach (["mccpp", "motd", "protocol", "client-version", "players",
              "max-players"] as $i) {
      if (count($val)) {
        $res[$i] = array_shift($val);
      } else {
        break;
      }
    }
		return $res;
	}
}
