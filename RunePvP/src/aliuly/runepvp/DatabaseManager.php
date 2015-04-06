<?php

namespace aliuly\runepvp;

class DatabaseManager {
    private $database;

    static function prepare($player) {
      return "'".\SQLite3::escapeString(strtolower($player))."'";
    }

    public function __construct($path){
      $this->database = new \SQLite3($path);
      $sql = "CREATE TABLE IF NOT EXISTS Scores(
		    player TEXT PRIMARY KEY,
		    level INTEGER NOT NULL,
		    kills INTEGER NOT NULL
		)";
      $this->database->exec($sql);
    }
    public function getTops($limit,$players = null) {
      $sql = "SELECT * FROM Scores";
      if ($players != null) {
	$sql .= " WHERE player IN (";
	$q = "";
	foreach ($players as $p) {
	  $sql .= $q.self::prepare($p);
	  $q = ",";
	}
	$sql .=")";
      }
      $sql .= " ORDER BY kills DESC";
      if ($limit) $sql .= " LIMIT ".intval($limit);
      $res = $this->database->query($sql);
      if ($res === false) return null;
      $tab = [];
      while (($row = $res->fetchArray(SQLITE3_ASSOC)) != false) {
	$tab[] = $row;
      }
      return $tab;
    }

    public function getScore($player) {
      $sql = "SELECT * FROM Scores WHERE player = ".self::prepare($player);
      $res = $this->database->query($sql);
      if ($res === false) return null;
      return $res->fetchArray(SQLITE3_ASSOC);
    }

    public function addScore($player,$level,$kills) {
      $sql = "INSERT INTO Scores (player,level,kills) VALUES (".
	self::prepare($player).", ".intval($level).", ".intval($kills).")";
      return $this->database->exec($sql);
    }

    public function updateScore($player,$level,$kills) {
      $sql ="UPDATE Scores SET level=".intval($level).", kills=".intval($kills).
	" WHERE (player = ".self::prepare($player).")";
      return $this->database->exec($sql);
    }

    public function delScore($player) {
      $sql ="DELETE FROM Scores WHERE player=".self::prepare($player);
      return $this->database->exec($sql);
    }
}
