<?php

namespace aliuly\common;

use pocketmine\Player;

use aliuly\common\Session;
use aliuly\common\MPMU;

use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerChatEvent;


/**
 * Chat Sessions
 *
 * NOTE, if GrabBag is available, it will use the GrabBag functions
 * This gives you a command line interface and also
 * reduces the number of listeners in use.
 */
class ChatSession extends Session {
  protected $apis;
  protected $chat;
  /**
   * @param PluginBase $owner - plugin that owns this session
   */
  public function __construct(PluginBase $owner) {
    $bag = $owner->getServer()->getPluginManager()->getPlugin("GrabBag");
    $this->apis = [ null, null ];
    if ($bag && $bag->isEnabled() && MPMU::apiCheck($bag->getDescription()->getVersion(),"2.3")) {
      if ($bag->api->getFeature("chat-utils")) $this->apis[0] = $bag->api;
      if ($bag->api->getFeature("mute-unmute")) $this->apis[1] = $bag->api;
      return;
    }
    parent::__construct($owner);
    $this->chat = true;
  }

  /**
   * Enable/Disable Chat globally
   * @param bool $mode - true, chat is active, false, chat is disabled
   */
  public function setGlobalChat($mode) {
    if ($this->apis[0] !== null) {
      $this->apis[0]->setGlobalChat($mode);
      return;
    }
    $this->chat = $mode;
  }
  /**
   * Returns global chat status
   * @return bool
   */
  public function getGlobalChat() {
    if ($this->apis[0] !== null) return $this->apis[0]->getGlobalChat();
    return $this->chat;
  }
  /**
   * Enable/Disable player's chat
   * @param Player $player
   * @param bool $mode - true, chat is active, false, chat is disabled
   */
  public function setPlayerChat(Player $player,$mode) {
    if ($this->apis[0] !== null) {
      $this->apis[0]->setPlayerChat($player, $mode);
      return;
    }
    if ($mode) {
      $this->unsetState("chat",$player);
    } else {
      $this->setState("chat",$player,false);
    }
  }
  /**
   * Returns player's chat status
   * @param Player $player
   * @return bool
   */
  public function getPlayerChat(Player $player) {
    if ($this->apis[0] !== null) return $this->apis[0]->getPlayerChat($player);

    return $this->getState("chat",$player,true);
  }
  /**
   * Mute/UnMute a player
   * @param Player $player
   * @param bool $mode - true is muted, false is unmuted
   */
  public function setMute(Player $player,$mode) {
    if ($this->apis[1] !== null) {
      $this->apis[1]->setMute($player, $mode);
      return;
    }
    if ($mode) {
      $this->setState("muted",$player,true);
    } else {
      $this->unsetState("muted",$player);
    }
  }
  /**
   * Returns a player mute status
   * @param Player $player
   * @return bool
   */
  public function getMute(Player $player) {
    if ($this->apis[1] !== null) return $this->apis[1]->getMute($player);
    return $this->getState("muted",$player,false);
  }

  public function onChat(PlayerChatEvent $ev) {
    //echo __METHOD__.",".__LINE__."\n";//##DEBUG
    if ($ev->isCancelled()) return;
    $p = $ev->getPlayer();

    if (!$this->chat) {
      $p->sendMessage(mc::_("Chat has been globally disabled!"));
      $ev->setCancelled();
      return;
    }
    if ($this->getState("muted",$p,false)) {
      $p->sendMessage(mc::_("You have been muted!"));
      $ev->setCancelled();
      return;
    }
    if (!$this->getState("chat",$p,true)) {
      $p->sendMessage(mc::_("Chat is disabled for you"));
      $ev->setCancelled();
      return;
    }
    $recvr = [];
    foreach ($ev->getRecipients() as $to) {
      if ($this->getState("chat",$to,true)) $recvr[] = $to;
    }
    $ev->setRecipients($recvr);
  }
}
