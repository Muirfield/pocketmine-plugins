<?php
namespace aliuly\killrate;

use aliuly\killrate\Main as KillRatePlugin;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use aliuly\killrate\common\mc;

//use pocketmine\plugin\PluginBase;
//use pocketmine\command\CommandExecutor;
//use pocketmine\command\ConsoleCommandSender;
//use pocketmine\command\CommandSender;
//use pocketmine\command\Command;



//use pocketmine\Server;
//use pocketmine\event\Listener;
//use pocketmine\utils\Config;

//use pocketmine\event\entity\EntityDamageEvent;
//use pocketmine\event\entity\EntityDeathEvent;
//use pocketmine\event\player\PlayerDeathEvent;
//use pocketmine\entity\Projectile;

//use aliuly\killrate\common\mc2;
//use aliuly\killrate\common\MPMU;
//use aliuly\killrate\common\MoneyAPI;

//use aliuly\killrate\api\KillRate as KillRateAPI;
//use aliuly\killrate\api\KillRateScoreEvent;
//use aliuly\killrate\api\KillRateResetEvent;
//use aliuly\killrate\api\KillRateNewStreakEvent;
//use aliuly\killrate\api\KillRateEndStreakEvent;
//use aliuly\killrate\api\KillRateBonusScoreEvent;

class RankMgr {
  protected $owner;
  protected $rankup;
  protected $defaultRank;
  public function __construct(KillRatePlugin $owner,$mode,$settings) {
    $this->owner = $owner;
    $this->rankup = null;
    if ($mode) {
			$this->rankup = $this->owner->getServer()->getPluginManager()->getPlugin("RankUp");
			if ($this->ranks === null) {
				$this->owner->getLogger()->error(TextFormat::RED.mc::_("RankUp plugin not found"));
				$this->owner->getLogger()->error(TextFormat::YELLOW.mc::_("ranks feature disabled"));
      } else {
        $this->defaultRank = $settings["default-rank"];
        if ($this->defaultRank) {
          $rank = $this->rankup->getRankStore()->getRankByName($this->defaultRank);
          if ($rank === false) {
            $this->owner->getLogger()->error(TextFormat::RED.mc::_("Default rank %1% not found", $this->defaultRank));
            $this->defaultRank = null;
          }
        } else {
          $this->defaultRank = null;
        }
      }
    }
  }
  public function resetRank(Player $player) {
    if ($this->rankup === null || $this->defaultRank === null) return;
    $rank = $this->rankup->getRankStore()->getRankByName($this->defaultRank);
    if (!$this->rankup->getPermManager()->addToGroup($player, $rank->getName())) {
      $this->owner->getLogger()->warning(mc::_("Unable to reset rank for %1%", $player));
    }
  }
  public function promote(Player $player, $newscore) {
    if ($this->rankup === null) return;
  	// OK, do we need to rank up?
		$nextrank = $this->rankup->getRankStore()->getNextRank($player);
		if ($newscore < $nextrank->getPrice()) return;
		// Yeah!  Levelling up!
		if ($this->ranks->getPermManager()->addToGroup($player,$nextrank->getName())) {
	    $this->owner->getServer()->broadcastMessage(TextFormat::BLUE.mc::_("%1% is now %2%!",$player->getDisplayName(),$nextrank->getName()));
		} else {
			$player->sendMessage(TextFormat::RED.mc::_("Unable to award level %1%", $nextrank->getName()));
		}
  }
}
