<?php
namespace aliuly\killrate;
use pocketmine\plugin\PluginBase;
use aliuly\common\PluginCallbackTask;

class MySqlMgr implements DatabaseManager {
	private $database;

	static function prepare($player) {
		return "'".\mysqli::real_escape_string(strtolower($player))."'";
	}
	public function close() {
		$this->database->close();
		unset($this->databse);
	}
	public function __construct(PluginBase $owner){
		$cf = $owner->getCfg("MySql");
		$this->database = new \mysqli($cf["host"],$cf["user"],$cf["password"],
												$cf["database"], $cf["port"]);
		if ($this->database->connect_error) {
			throw new \RuntimeException("Invalid MySql settings");
			return;
		}
		$sql = "CREATE TABLE IF NOT EXISTS Scores (
			player VARCHAR(16) NOT NULL,
			type VARCHAR(128) NOT NULL,
			count INT NOT NULL,
			PRIMARY KEY (player,type)
		)";
		$this->database->query($sql);
		$owner->getServer()->getScheduler()->scheduleRepeatingTask(new PluginCallbackTask($owner,[$this,"pingMySql"]),600);
		$owner->getLogger()->info("Connected to MySQL server");
	}

	public function getTops($limit,$players,$scores) {
		$sql = "SELECT * FROM Scores";
		$sql .= " WHERE type = ".self::prepare($scores);
		if ($players != null) {
			$sql .= " AND player IN (";
			$q = "";
			foreach ($players as $p) {
				$sql .= $q.self::prepare($p);
				$q = ",";
			}
			$sql .=")";
		}
		$sql .= " ORDER BY count DESC";
		if ($limit) $sql .= " LIMIT ".intval($limit);
		//echo $sql."\n";
		$res = $this->database->query($sql);
		if ($res === false) return null;
		$tab = [];
		while (($row = $res->fetch_assoc()) != null) {
			$tab[] = $row;
		}
		$res->free();
		return $tab;
	}

	public function getScores($player) {
		$sql = "SELECT * FROM Scores WHERE player = ".self::prepare($player);
		$res = $this->database->query($sql);
		if ($res === false) return null;
		$tab = [];
		while (($row = $res->fetch_assoc()) != null) {
			$tab[] = $row;
		}
		$res->free();
		return $tab;
	}
	public function getScore($player,$type) {
		$sql = "SELECT * FROM Scores WHERE player = ".self::prepare($player).
			  " AND type = ".self::prepare($type);
		$res = $this->database->query($sql);
		$ret = $res->fetch_assoc();
		$res->free();
		return $ret;
	}
	public function insertScore($player,$type,$cnt) {
		$sql = "INSERT INTO Scores (player,type,count) VALUES (".
			  self::prepare($player).", ".self::prepare($type).", ".intval($cnt).
			  ")";
		return $this->database->query($sql);
	}

	public function updateScore($player,$type,$cnt) {
		$sql ="UPDATE Scores SET count=".intval($cnt).
			  " WHERE (player = ".self::prepare($player)." AND type = ".
			  self::prepare($type).")";
		return $this->database->query($sql);
	}

	public function delScore($player,$type = null) {
		$sql ="DELETE FROM Scores WHERE player=".self::prepare($player);
		if ($type !== null) {
			$sql .= " AND type = ".self::prepare($type);
		}
		return $this->database->query($sql);
	}
	public function pingMySql() {
		if (isset($this->database)) $this->database->ping();
	}
}
