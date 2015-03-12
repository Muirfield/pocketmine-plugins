<?php
namespace pmimporter;
use pmimporter\Blocks;

abstract class Entities {
  protected static $ids = [];

  public static function __init() {
    if (count(self::$ids)) return; // Only read them once...

    foreach (["Chicken","Cow","Creeper","Enderman","MushroomCow","Pig",
	      "PigZombie","Sheep","Silverfish","Skeleton","Slime",
	      "Spider","Villager","Wolf","Zombie","Arrow","Snowball",
	      "ThrownEgg","Item","Minecart","PrimedTnt",
	      "FallingSand","Painting"] as $eid) {
      self::$ids[$eid] = $eid;
      define("EID_".strtoupper(Blocks::from_camel_case($eid)),$eid);
    }
  }
  public static function getId($id) {
    if (isset(self::$ids[$id])) return self::$ids[$id];
    return null;
  }
}
