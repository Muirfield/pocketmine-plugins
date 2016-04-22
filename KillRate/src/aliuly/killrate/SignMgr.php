<?php
namespace aliuly\killrate;

use aliuly\killrate\Main as KillRatePlugin;

use aliuly\killrate\common\PluginCallbackTask;
use aliuly\killrate\common\MPMU;
use aliuly\killrate\common\mc;
use aliuly\killrate\common\SignUtils;
use aliuly\killrate\common\MoneyAPI;

use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\block\Block;
use pocketmine\tile\Sign;
use pocketmine\event\Listener;


use pocketmine\network\protocol\BlockEventPacket;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;

class SignMgr implements Listener {
  protected $owner;
  protected $signtxt;
  protected $formats;

  public function __construct(KillRatePlugin $owner,$cfg) {
    $this->owner = $owner;

    $this->signtxt = $cfg["signs"];
    $this->formats = $cfg["formats"];

    $this->owner->getServer()->getPluginManager()->registerEvents($this, $this->owner);
    if ($cfg["settings"]["dynamic-updates"] && $cfg["settings"]["dynamic-updates"] > 0) {
      $this->owner->getServer()->getScheduler()->scheduleRepeatingTask(
        new PluginCallbackTask($this->owner,[$this,"updateTimer"],[]),$cfg["settings"]["dynamic-updates"]
      );
    }
  }

	//////////////////////////////////////////////////////////////////////
	//
	// Sign related functionality
	//
	//////////////////////////////////////////////////////////////////////
	public function playerTouchSign(PlayerInteractEvent $ev){
		if($ev->getBlock()->getId() != Block::SIGN_POST &&
			$ev->getBlock()->getId() != Block::WALL_SIGN) return;
		$tile = $ev->getPlayer()->getLevel()->getTile($ev->getBlock());
		if(!($tile instanceof Sign)) return;
		$sign = $tile->getText();
		if (!isset($this->signtxt[$sign[0]])) return;
		$pl = $ev->getPlayer();
		if (!MPMU::access($pl,"killrate.signs.use")) return;
		$this->activateSign($pl,$tile);
	}
	public function placeSign(SignChangeEvent $ev){
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
		if($ev->getBlock()->getId() != Block::SIGN_POST &&
			$ev->getBlock()->getId() != Block::WALL_SIGN) return;
		$tile = $ev->getPlayer()->getLevel()->getTile($ev->getBlock());
		if(!($tile instanceof Sign)) return;
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$sign = $ev->getLines();
		if (!isset($this->signtxt[$sign[0]])) return;
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$pl = $ev->getPlayer();
		if (!MPMU::access($pl,"killrate.signs.place")) {
			//echo __METHOD__.",".__LINE__."\n";//##DEBUG
			SignUtils::breakSignLater($this->owner,$tile);
			return;
		}
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$pl->sendMessage(mc::_("Placed [KillRate] sign"));
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
		$this->owner->getServer()->getScheduler()->scheduleDelayedTask(
      new PluginCallbackTask($this->owner,[$this,"updateTimer"],[]),
      10
    );
	}
	public function updateTimer() {

		foreach ($this->owner->getServer()->getLevels() as $lv) {
			if (count($lv->getPlayers()) == 0) continue;
			foreach ($lv->getTiles() as $tile) {
				if (!($tile instanceof Sign)) continue;
				$sign = $tile->getText();
				if (!isset($this->signtxt[$sign[0]])) continue;
				foreach ($lv->getPlayers() as $pl) {
					$this->activateSign($pl,$tile);
				}
			}
		}
		//echo __METHOD__.",".__LINE__."\n";//##DEBUG
	}
	private function updateSign($pl,$tile,$text) {
		$pk = new BlockEventPacket();
		$data = $tile->getSpawnCompound();
		$data->Text1 = new StringTag("Text1",$text[0]);
		$data->Text2 = new StringTag("Text2",$text[1]);
		$data->Text3 = new StringTag("Text3",$text[2]);
		$data->Text4 = new StringTag("Text4",$text[3]);
		$nbt = new NBT(NBT::LITTLE_ENDIAN);
		$nbt->setData($data);

		$pk->x = $tile->getX();
		$pk->y = $tile->getY();
		$pk->z = $tile->getZ();
		$pk->namedtag = $nbt->write();
		$pl->dataPacket($pk);
	}
	public function activateSign($pl,$tile) {
		$sign = $tile->getText();
		$mode = $this->signtxt[$sign[0]];
		switch ($mode) {
			case "stats":
				$name = $pl->getName();
				$text = ["","","",""];
				$text[0] = mc::_("Stats: %1%",$name);

				$l = 1;
				foreach (["Player"=>mc::_("Kills: "),
							 "points"=>mc::_("Points: ")] as $i=>$j) {
					$score = $this->owner->getScoreV2($name,$i);
					if ($score && isset($score["count"])) {
						$score = $score["count"];
					} else {
						$score = "N/A";
					}
					$text[$l++] = $j.$score;
				}
        if ($this->owner->getMoneyPlugin() !== null) {
				  $text[$l++] = mc::_("Money: ").
					     MoneyAPI::getMoney($this->owner->getMoneyPlugin(),$name);
        }
				break;
			case "online-tops":
				$text = $this->topSign(true,"default",mc::_("Top Online"),$sign);
				break;
			case "rankings":
				$text = $this->topSign(false,"default",mc::_("Top Players"),$sign);
				break;
			case "rankings-names":
				$text = $this->topSign(false,"names",mc::_("Top Names"),$sign);
				break;
			case "rankings-points":
				$text = $this->topSign(false,"scores",mc::_("Top Scores"),$sign);
				break;
			case "online-top-names":
				$text = $this->topSign(true,"names",mc::_("On-line Names"),$sign);
				break;
			case "online-top-points":
				$text = $this->topSign(true,"scores",mc::_("On-line Scores"),$sign);
				break;
			default:
				return;

		}
		$this->updateSign($pl,$tile,$text);
	}

	protected function topSign($mode,$fmt,$title,$sign) {
		$col = "points";
		if ($sign[1] != "") $title = $sign[1];
		if ($sign[2] != "") $col = $sign[2];
		if ($sign[3] != "" && isset($this->formats[$sign[3]])) {
			$fmt = $this->formats[$sign[3]];
		} else {
			$fmt = $this->formats[$fmt];
		}
		$text = ["","","",""];
		if ($title == "^^^") {
			$cnt = 4;
			$start = 0;
		} else {
			$text[0] = $title;
			$cnt = 3;
			$start = 1;
		}
		$res = $this->owner->getRankings($cnt,$mode,$col);
		if ($res == null) {
			$text[2] = mc::_("NO STATS FOUND!");
		} else {
			$i = 1; $j = $start;
			foreach ($res as $r) {
				$tr = [
					"{player}" => $r["player"],
					"{count}" => $r["count"],
					"{sname}" => substr($r["player"],0,8),
					"{n}" => $i++,
				];
				$text[$j++] = strtr($fmt,$tr);
			}
		}
		return $text;
	}
}
