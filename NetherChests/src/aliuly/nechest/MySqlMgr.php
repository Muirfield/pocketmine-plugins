<?php
namespace aliuly\nechest;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use aliuly\nechest\common\PluginCallbackTask;


class MySqlMgr implements DatabaseManager {
  protected $isGlobal;
  protected $owner;
  private $database;

  protected function prepare($player) {
		return "'".$this->database->real_escape_string(strtolower($player))."'";
	}
  public function close() {
    $this->database->close();
    unset($this->database);
  }

	public function __construct(PluginBase $owner,$cf) {
    $this->owner = $owner;
    $this->isGlobal = $cf["settings"]["global"];
    $this->database = new \mysqli($cf["MySql"]["host"],$cf["MySql"]["user"],
                        $cf["MySql"]["password"],$cf["MySql"]["database"],
                        $cf["MySql"]["port"]);
    if ($this->database->connect_error) {
      throw new \RuntimeException("Invalid MySql settings");
      return;
    }
    $sql = "CREATE TABLE IF NOT EXISTS NetherChests (
      player VARCHAR(16) NOT NULL,
      world VARCHAR(128) NOT NULL,
      slot INT NOT NULL,
      id INT NOT NULL,
      damage INT NOT NULL,
      count INT NOT NULL,
      PRIMARY KEY (player,world,slot)
    )";
    $this->database->query($sql);
    $owner->getServer()->getScheduler()->scheduleRepeatingTask(new PluginCallbackTask($owner,[$this,"pingMySql"]),600);
    $owner->getLogger()->info("Connected to MySQL server");
  }
  public function pingMySql() {
    if (isset($this->database)) $this->database->ping();
  }

  public function saveInventory(Player $player,Inventory $inv) {
    $n = trim(strtolower($player->getName()));
    if ($n === "") return false;
    if ($this->isGlobal)
      $ln = "*";
    else
      $ln = trim(strtolower($player->getLevel()->getName()));

    // Save inventory...
    $sql = "DELETE FROM NetherChests WHERE player=".$this->prepare($n).
        " AND world=".$this->prepare($ln);
    $this->database->query($sql);

    foreach ($inv->getContents() as $slot=>&$item) {
      $sql = "INSERT INTO NetherChests (player,world,slot,id,damage,count) VALUES (".
            $this->prepare($n).", ".
            $this->prepare($ln).", ".
            $slot.", ".
            $item->getId().", ".
            $item->getDamage().", ".
            $item->getCount().
            ")";
      $this->database->query($sql);
    }
    $inv->clearAll();
    return true;
  }
  public function loadInventory(Player $player,Inventory $inv) {
    $n = trim(strtolower($player->getName()));
    if ($n === "") return false;
    if ($this->isGlobal)
      $ln = "*";
    else
      $ln = trim(strtolower($player->getLevel()->getName()));

		$inv->clearAll();
    $sql = "SELECT slot,id,damage,count FROM NetherChests WHERE player = ".
          $this->prepare($n) . " AND world = ".$this->prepare($ln);
    $res = $this->database->query($sql);
    if ($res === false) return false;
    while (($row = $res->fetch_assoc()) != null) {
      $inv->setItem($row["slot"],Item::get($row["id"],$row["damage"],$row["count"]));
    }
    $res->free();
		return true;
	}
}
