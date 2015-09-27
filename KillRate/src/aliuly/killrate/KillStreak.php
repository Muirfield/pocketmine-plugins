<?php
namespace aliuly\killrate;

use aliuly\killrate\Main as KillRatePlugin;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use aliuly\killrate\common\mc;

use aliuly\killrate\api\KillRateNewStreakEvent;
use aliuly\killrate\api\KillRateEndStreakEvent;
use aliuly\killrate\api\KillRateBonusScoreEvent;
use aliuly\killrate\common\MoneyAPI;

class KillStreak {
  protected $owner;
  protected $enabled;
  protected $money;
  protected $minkills;

  public function __construct(KillRatePlugin $owner,$mode,$settings,$money) {
    $this->owner = $owner;
    $this->enabled = $mode;
    $this->minkills = $settings["min-kills"];
    $this->money = $money;
  }

  public function endStreak(Player $player) {
    if (!$this->enabled) return;
    $n = strtolower($player->getName());
    $newstreak = $this->owner->getScoreV2($n,"streak");
    if ($newstreak == 0 || $newstreak < $this->minkills) return;
    $this->owner->getServer()->getPluginManager()->callEvent(
        $ev = new KillRateEndStreakEvent($this->owner,$player,$newstreak)
    );
    if ($ev->isCancelled()) return;
    $oldstreak = $this->owner->getScoreV2($n,"best-streak");
    if ($oldstreak == 0) {
      $this->owner->setScore($n,$newstreak,"best-streak");
      $this->owner->getServer()->broadcastMessage(mc::_("%1% ended his first kill-streak at %2% kills", $player->getDisplayName(), $newstreak));
    } elseif ($newstreak > $oldstreak) {
      $this->owner->setScore($n,$newstreak,"best-streak");
      $this->owner->getServer()->broadcastMessage(mc::_("%1% beat previous streak record of %2% at %3% kills", $player->getDisplayName(), $oldstreak, $newstreak));
    }
    $this->owner->delScore($n,"streak");
  }
  public function scoreStreak(Player $player) {
    if (!$this->enabled) return false;
    $n = strtolower($player->getName());
    $streak = $this->owner->updateDb($n,"streak");
    if ($streak < $this->minkills) return false;

    $this->owner->getServer()->getPluginManager()->callEvent(
          new KillRateNewStreakEvent($this->owner,$player,$streak)
    );
    $this->owner->getServer()->broadcastMessage(TextFormat::YELLOW.mc::_("%1% has a %2%-kill streak",$player->getDisplayName(),$streak));
    if ($this->money === null) return true;

    list($points,$money) = $this->owner->getPrizes("streak");
    $this->owner->getServer()->getPluginManager()->callEvent(
            $ev = new KillRateBonusScoreEvent($this->owner,$player,$money)
    );
    if ($ev->isCancelled()) return true;
    $player->sendMessage(TextFormat::GREEN.
                    mc::_("You earn an additional $%1% for being in kill-streak!",$ev->getMoney()));
    MoneyAPI::grantMoney($this->money,$player,$ev->getMoney());
    return true;
  }
}
