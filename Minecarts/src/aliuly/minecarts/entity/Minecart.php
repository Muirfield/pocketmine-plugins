<?php
namespace aliuly\minecarts\entity;
use pocketmine\item\Item as ItemItem;
use pocketmine\event\entity\EntityDamageByEntityEvent;
//use pocketmine\network\protocol\AddMobPacket;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\nbt\tag\Short;
//use pocketmine\entity\Animal;
use pocketmine\entity\Vehicle;

// Need to prevent placing on non-Rails


class Minecart extends Vehicle {
  const NETWORK_ID=84;
  public $width = 0.98;
  public $length = 0.8125;
  public $height = 0.7;

  public function getName(){
    echo __METHOD__.__LINE__."\n";
    return "Minecart";
  }
  public function spawnTo(Player $player){
    echo __METHOD__.__LINE__."\n";
    $pk = new AddEntityPacket();
    $pk->eid = $this->getId();
    $pk->type = Minecart::NETWORK_ID;
    $pk->x = $this->x;
    $pk->y = $this->y;
    $pk->z = $this->z;
    $pk->did = 0;
    //$pk->speedX = $this->motionX;
    //$pk->speedY = $this->motionY;
    //$pk->speedZ = $this->motionZ;
    $player->dataPacket($pk);

    $player->addEntityMotion($this->getId(), $this->motionX, $this->motionY, $this->motionZ);

    parent::spawnTo($player);
  }

  public function getData(){ //TODO
    echo __METHOD__.__LINE__."\n";
    $flags = 0;
    $flags |= $this->fireTicks > 0 ? 1 : 0;
    //$flags |= ($this->crouched === true ? 0b10:0) << 1;
    //$flags |= ($this->inAction === true ? 0b10000:0);
    $d = [
	  0 => ["type" => 0, "value" => $flags],
	  1 => ["type" => 1, "value" => $this->airTicks],
	  16 => ["type" => 0, "value" => 0],
	  17 => ["type" => 6, "value" => [0, 0, 0]],
	  ];

    return $d;
  }
  public function getDrops(){
    echo __METHOD__.__LINE__."\n";
    return  [ItemItem::get(ItemItem::MINECART, 0, 1)];
  }

  protected function initEntity(){
    echo __METHOD__.__LINE__."\n";
    if(isset($this->namedtag->HealF)){
      $this->namedtag->Health = new Short("Health", (int) $this->namedtag["HealF"]);
      unset($this->namedtag->HealF);
    }

    if(!isset($this->namedtag->Health) or !($this->namedtag->Health instanceof Short)){
      $this->namedtag->Health = new Short("Health", $this->getMaxHealth());
    }

    $this->setHealth($this->namedtag["Health"]);
  }
  public function attack($damage, $source = EntityDamageEvent::CAUSE_MAGIC){
    echo __METHOD__.__LINE__."\n";
    if($this->attackTime > 0 or $this->noDamageTicks > 0){
      $lastCause = $this->getLastDamageCause();
      if($lastCause instanceof EntityDamageEvent and $lastCause->getDamage() >= $damage){
	if($source instanceof EntityDamageEvent){
	  $source->setCancelled();
	  $this->server->getPluginManager()->callEvent($source);
	  $damage = $source->getFinalDamage();
	  if($source->isCancelled()){
	    return;
	  }
	}else{
	  return;
	}
      }else{
	return;
      }
    }elseif($source instanceof EntityDamageEvent){
      $this->server->getPluginManager()->callEvent($source);
      $damage = $source->getFinalDamage();
      if($source->isCancelled()){
	return;
      }
    }

    $this->setLastDamageCause($source);

    if($source instanceof EntityDamageByEntityEvent){
      $e = $source->getDamager();
      $deltaX = $this->x - $e->x;
      $deltaZ = $this->z - $e->z;
      $yaw = atan2($deltaX, $deltaZ);
      $this->knockBack($e, $damage, sin($yaw), cos($yaw), $source->getKnockBack());
    }

    $this->setHealth($this->getHealth() - $damage);

    $pk = new EntityEventPacket();
    $pk->eid = $this->getId();
    $pk->event = $this->getHealth() <= 0 ? 3 : 2; //Ouch!
    Server::broadcastPacket($this->hasSpawned, $pk);

    $this->attackTime = 10; //0.5 seconds cooldown
  }
  public function knockBack(Entity $attacker, $damage, $x, $z, $base = 0.4){
    echo __METHOD__.__LINE__."\n";
    $f = sqrt($x ** 2 + $z ** 2);

    $motion = new Vector3($this->motionX, $this->motionY, $this->motionZ);

    $motion->x /= 2;
    $motion->y /= 2;
    $motion->z /= 2;
    $motion->x += ($x / $f) * $base;
    $motion->y += $base;
    $motion->z += ($z / $f) * $base;

    if($motion->y > $base){
      $motion->y = $base;
    }

    $this->setMotion($motion);
  }
  public function heal($amount, $source = EntityRegainHealthEvent::CAUSE_MAGIC){
    $this->setHealth($this->getHealth() + $amount);
  }
}