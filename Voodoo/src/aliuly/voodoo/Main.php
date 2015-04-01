<?php
namespace aliuly\voodoo;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\entity\Zombie;
use pocketmine\math\Vector3;
use pocketmine\event\entity\EntityDamageEvent;

// Used for spawning zombies
use pocketmine\Player;
use pocketmine\command\CommandExecutor;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\entity\Entity;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Float;
use pocketmine\utils\Random;
use pocketmine\level\Position;


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
	  //$ent->setMotion($dir);
	  $dir = $dir->multiply($this->speed);
	  //echo "DIRVECTOR: ".$dir->distance(new Vector3(0,0,0))."\n";

	  $ent->move($dir->getX(),$dir->getY(),$dir->getZ());
	}
      }
    }

  }
  public function onEnable(){
    $this->range = 32;
    $this->speed = 0.4;
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    $this->getServer()->getScheduler()->scheduleRepeatingTask(new UpdateTask($this),30);
  }
  // DEBUG
  private function inGame(CommandSender $sender,$msg = true) {
    if ($sender instanceof Player) return true;
    if ($msg) $sender->sendMessage("You can only use this command in-game");
    return false;
  }
  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
    if (!$this->inGame($sender)) return true;
    try {
      $bl = $sender->getTargetBlock(100,[0,8,9,10,11]);
    } catch(\Exception $e) {
      if(\pocketmine\DEBUG > 1){
	$this->getLogger()->alert("TargetBlock error!");
      }
      $sender->sendMessage("Unable to find a suitable block to spawn zombie");
      return true;
    }

    if ($bl === null) {
      $sender->sendMessage("You must be pointing at some ground");
      return true;
    }
    $mot = (new Random())->nextSignedFloat() * M_PI * 2;
    $pos = new Position($bl->getX(),$bl->getY()+5,$bl->getZ(),$sender->getLevel());
    $nbt =
      new Compound("",
		   [
		    "Pos"=>new Enum("Pos",[
					   new Double("",$pos->x+0.5),
					   new Double("",$pos->y),
					   new Double("",$pos->z+0.5)
					   ]),
		    "Motion"=>new Enum("Motion",[
						 new Double("",-sin($mot)*0.02),
						 new Double("", 0.2),
						 new Double("",-cos($mot)*0.02)
						 ]),
		    "Rotation" => new Enum("Rotation", [
							new Float("", 0),
							new Float("", 0)
							]),
		    // IsVillager Byte
		    // IsBaby Byte
		    // ConversionTime Int
		    //"CanBreakDoors" => new Byte("CanBreakDoors", 0),
		    ]);
    $entity = Entity::createEntity("Zombie", $pos->getLevel()->getChunk($pos->x >> 4, $pos->z >> 4),$nbt);
    $entity->namedtag->setName("VoodooZombie");
    $entity->spawnToAll();
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
