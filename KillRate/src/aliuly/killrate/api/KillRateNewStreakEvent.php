<?php
namespace aliuly\killrate\api;

use aliuly\killrate\Main as KillRatePlugin;
use pocketmine\event\Cancellable;
use pocketmine\Player;
use aliuly\killrate\api\KillRateEvent;

/**
 * Triggered when the player beats previous streak
 */
class KillRateNewStreakEvent extends KillRateEvent {
  public static $handlerList = null;
  /** @var Player */
  private $player;
  private $victim;
  private $streak;
  /**
   * @param KillRatePlugin $plugin - plugin owner
   * @param Player $player - player making the score
   * @param Player $victim - last victim
   * @param int $newstreak - streak
   */
  public function __construct(KillRatePlugin $plugin, Player $player, Player $victim, $streak) {
    parent::__construct($plugin);
    $this->player = $player;
    $this->victim = $victim;
    $this->streak = $streak;
  }
  /**
   * @return Player
   */
  public function getPlayer() {
    return $this->player;
  }
  public function getVictim() {
    return $this->victim;
  }
  public function getStreak() {
    return $this->streak;
  }
}
