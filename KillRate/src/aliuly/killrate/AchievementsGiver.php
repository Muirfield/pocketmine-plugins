<?php
namespace aliuly\killrate;

use aliuly\killrate\Main as KillRatePlugin;

use pocketmine\Achievement;
use pocketmine\Player;

class AchievementsGiver {
  protected $owner;
  protected $enabled;
  public function __construct(KillRatePlugin $owner,$mode) {
    $this->owner = $owner;
    $this->enabled = $mode;
    if ($this->enabled) {
      Achievement::add("killer","First Blood!",[]);
      Achievement::add("serialKiller","Killer Streak!",["killer"]);
      Achievement::add("ranked1","Ranked #1!",["killer"]);
      Achievement::add("kill10","Achieved 10 Kills!",["killer"]);
      Achievement::add("kill100","Achieved 100 Kills!",["kill10"]);
      Achievement::add("kill1000","Achieved 1,000 Kills!",["kill100"]);
    }
  }
  public function awardSerialKiller(Player $player) {
    if (!$this->enabled) return;
    $player->awardAchievement("serialKiller");
  }

  public function awardKills(Player $player, $vic, $kills) {
    if (!$this->enabled || !$kills) return;

    $player->awardAchievement("killer");
    if ($vic == "Player") {
      if ($kills >= 10) $player->awardAchievement("kill10");
      if ($kills >= 100) $player->awardAchievement("kill100");
      if ($kills >= 1000) $player->awardAchievement("kill1000");
    }

    $res = $this->owner->getRankings(1);
    if ($res !== null && $res[0]["player"] == strtolower($player->getName())) {
      // Achieved #1 ranking!
      $player->awardAchievement("ranked1");
    }
  }

}
