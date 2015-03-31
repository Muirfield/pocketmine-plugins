<?php
namespace aliuly\voodoo;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\entity\Zombie;
use pocketmine\math\Vector3;
use pocketmine\event\entity\EntityDamageEvent;

use pocketmine\command\CommandExecutor;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

//class Main extends PluginBase implements Listener {
class Main extends PluginBase implements Listener,CommandExecutor {
  protected $range;
  protected $speed;

  public function updateMobs() {
    foreach ($this->getServer()->getLevels() as $lv) {
      $ps = $lv->getPlayers();
      if (count($ps) == 0) continue;
      foreach ($lv->getEntities() as $ent) {
	if (!($ent instanceof Zombie)) continue;
	$dist = null;
	foreach ($ps as $pl) {
	  $cd = $ent->distance($pl);
	  if ($cd > $this->range) continue;
	  if ($dist && $cd > $dist[0]) continue;
	  $dist = [ $cd, &$pl ];
	}
	if ($dist == null) continue; // No target in range!
	// Move mob...
	if ($dist[0] < 2) { // ATTACK!
	  //echo "Attacking ".$pl->getName()."\n";
	  $pl->attack(mt_rand(1,2),EntityDamageEvent::CAUSE_ENTITY_ATTACK);
	} else {
	  $dir = $dist[1]->subtract($ent);
	  $dir = $dir->divide($dist[0]); // Normalized...
	  // Calculate pitch/yaw...
	  //echo "Pitch: ".$ent->pitch."\n";
	  //echo "Yaw: ".$ent->yaw."\n";
	  //echo "DX: ".$dir->getX()."\n";
	  //echo "DY: ".$dir->getY()."\n";
	  //echo "DZ: ".$dir->getZ()."\n";
	  $newYaw = rad2deg(atan2(-$dir->getX(),$dir->getZ()));
	  //echo "NewYaw: $newYaw\n";
	  if ($dir->getY() > 0.5)
	    $newPitch = 45;
	  elseif ($dir->getY() < -0.5)
	    $newPitch = -45;
	  else
	    $newPitch = 0;
	  $ent->setRotation($newYaw,$newPitch);

	  //echo "Approaching ".$pl->getName()."\n";
	  $dir = $dir->multiply($this->speed);
	  //echo "DIRVECTOR: ".$dir->distance(new Vector3(0,0,0))."\n";

	  $ent->move($dir->getX(),$dir->getY(),$dir->getZ());
	}
      }
    }

  }
  public function onEnable(){
    $this->range = 32;
    $this->speed = 0.2;
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    $this->getServer()->getScheduler()->scheduleRepeatingTask(new UpdateTask($this),30);
  }
  // DEBUG
  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
    if (isset($args[0]) && $args[0] == "spawn") {
      return true;
    }

    return true;
  }
}

// Entity functions
// fastMove($dx,$dy,$dz)
// move($dx,$dy,$dz)
// setRotation($yaw,$pitch)
// setMotion(Vector3 $motion)
// getMetadata($key) (set,has,remove)
// attack($damage,$source = EntityDamageEvent::CAUSE_ENTITY_ATTACK=1
//use pocketmine\event\entity\EntityDamageByEntityEvent;
//use pocketmine\event\entity\EntityDamageEvent;
