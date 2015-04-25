<?php
namespace aliuly\grabbag;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\command\ConsoleCommandSender;


class RconTask extends AsyncTask {
	const RCTYPE_COMMAND = 2;
	const RCTYPE_AUTH = 3;

	protected $sender;
	protected $server;
	protected $cmd;

	private $sock;
	private $id;

	public function __construct($sender,$server,$cmd) {
		$this->sender =  $sender;
		$this->server = $server;
		$this->cmd = $cmd;
		$this->sock = false;
	}
	public function close($msg = "") {
		$this->setResult($msg);
		if ($this->sock) {
			fclose($this->sock);
			$this->sock = false;
		}
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
		$payload = rtrim(fread($this->sock,$size-8));
		return array($id,$type,$payload);
	}

	public function onRun() {
		$srv = preg_split('/\s+/',$this->server);
		if (!($this->sock = @fsockopen($srv[0],intval($srv[1]), $errno, $errstr, 30))) {
			$this->close("Unable to open socket: $errstr ($errno)");
			return;
		}
		stream_set_timeout($this->sock,3,0);
		$this->id = 0;

		$pktid = $this->writePkt(self::RCTYPE_AUTH,$srv[2]);
		$ret = $this->readPkt();
		if (is_null($ret)) {
			$this->close("Protocol error");
			return;
		}
		if ($ret[0] == -1) {
			$this->close("Authentication failure");
			return;
		}
		$id = $this->writePkt(self::RCTYPE_COMMAND,$this->cmd);
		$ret = $this->readPkt();
		if (is_null($ret)) {
			$this->close("Protocol error");
			return;
		}
		list ($rid,$type,$payload) = $ret;
		if ($rid !== $id) {
			$this->close("Sequencing Error");
			return;
		}
		$this->close($payload);
	}

	public function onCompletion(Server $server) {
		$sender = $server->getPlayer($this->sender);
		if (!$sender) {
			$sender = new ConsoleCommandSender();
		}
		$sender->sendMessage($this->getResult());
	}
}
