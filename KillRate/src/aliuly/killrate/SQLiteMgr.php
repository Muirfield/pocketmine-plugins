<?php

namespace aliuly\killrate;
use pocketmine\plugin\PluginBase;

class SQLiteMgr implements DatabaseManager {
	private $database;

	static function prepare($player) {
		return "'".\SQLite3::escapeString(strtolower($player))."'";
	}
	public function close() {
		$this->database->close();
		unset($this->database);
	}

	public function __construct(PluginBase $owner,$ignored){
		$path = $owner->getDataFolder()."stats.sqlite3";
		$this->database = new \SQLite3($path);
		$sql = "CREATE TABLE IF NOT EXISTS Scores (
			player TEXT NOT NULL,
			type TEXT NOT NULL,
			count INTEGER NOT NULL,
			PRIMARY KEY (player,type)
		)";
		$this->database->exec($sql);
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
		while (($row = $res->fetchArray(SQLITE3_ASSOC)) != false) {
			$tab[] = $row;
		}
		return $tab;
	}

	public function getScores($player) {
		$sql = "SELECT * FROM Scores WHERE player = ".self::prepare($player);
		$res = $this->database->query($sql);
		if ($res === false) return null;
		$tab = [];
		while (($row = $res->fetchArray(SQLITE3_ASSOC)) != false) {
			$tab[] = $row;
		}
		return $tab;
	}
	public function getScore($player,$type) {
		$sql = "SELECT * FROM Scores WHERE player = ".self::prepare($player).
			  " AND type = ".self::prepare($type);
		$res = $this->database->query($sql);
		return $res->fetchArray(SQLITE3_ASSOC);
	}
	public function insertScore($player,$type,$cnt) {
		$sql = "INSERT INTO Scores (player,type,count) VALUES (".
			  self::prepare($player).", ".self::prepare($type).", ".intval($cnt).
			  ")";
		return $this->database->exec($sql);
	}

	public function updateScore($player,$type,$cnt) {
		$sql ="UPDATE Scores SET count=".intval($cnt).
			  " WHERE (player = ".self::prepare($player)." AND type = ".
			  self::prepare($type).")";
		return $this->database->exec($sql);
	}

	public function delScore($player,$type = null) {
		$sql ="DELETE FROM Scores WHERE player=".self::prepare($player);
		if ($type !== null) {
			$sql .= " AND type = ".self::prepare($type);
		}
		return $this->database->exec($sql);
	}
}
