<?php
/*
__PocketMine Plugin__
name=NetherPortal
description=Stand on Nether Reactor to teleport
version=0.2
author=Alex
class=NetherPortal
apiversion=9,10,11
*/

/**
 ** # META_NAME
 **
 **     META_NAME META_VERSION
 **     Copyright (C) 2013 Alejandro Liu  
 **     All Rights Reserved.
 **
 **     This program is free software: you can redistribute it and/or modify
 **     it under the terms of the GNU General Public License as published by
 **     the Free Software Foundation, either version 2 of the License, or
 **     (at your option) any later version.
 **
 **     This program is distributed in the hope that it will be useful,
 **     but WITHOUT ANY WARRANTY; without even the implied warranty of
 **     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 **     GNU General Public License for more details.
 **
 **     You should have received a copy of the GNU General Public License
 **     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 **
 ** * * *
 **
 ** NetherPortal is a simple Teleport plugin used to create portals between
 ** worlds.
 **
 ** # Changes
 **
 ** * 0.1 : Initial version
 ** * 0.2 : Completely revamped
 **
 ** # TODO
 **
 ** # Known Issues
 **
 ** - Garbage collector load maps to test if they are available.
 **
 **/

class NetherPortal implements Plugin{
  private $api, $config, $server;

  private $blockID;
  private $portals;
  private $targets;
  private $marker;

  public function __construct(ServerAPI $api, $server = false){
    $this->api = $api;
    $this->server = ServerAPI::request();
    $this->targets = array();
    $this->marker = NULL;
  }

  public function init(){
    $this->config = new Config($this->api->plugin->configPath($this).
			       "config.yml", CONFIG_YAML,
			       array(
				     "portals" => array(),
				     'ItemID' => '247',
				     ));
    // 247 = Nether Reactor Core
    $this->blockID = $this->config->get('ItemID');
    $this->portals = $this->config->get('portals');

    $this->api->event("entity.move", array($this, "entitymove"));
    $this->api->addHandler('player.block.break',array($this,"brkblkH"),15);
    $this->api->addHandler('player.block.place',array($this,"plcblkH"),15);

    $this->api->console->register("netherportal", "[subcmd] ...",array($this, "command"));
    $this->api->console->alias("np", "netherportal");
    $this->api->console->alias("npls", "netherportal ls");
    $this->api->console->alias("npto", "netherportal target");
    $this->api->console->alias("npgc", "netherportal gc");
    $this->api->console->alias("npnew", "netherportal new");
    $this->api->console->alias("nprm", "netherportal delete");
    $this->api->console->alias("npgo","netherportal set");
  }
  private function usage($cmd) {
    $output = '';
    $output .= "Usage: /$cmd <commmand> [parameters...]\n";
    $output .= "/$cmd ls [pattern]: List portals\n";
    $output .= "/$cmd target [world]: set target for new blocks\n";
    $output .= "/$cmd gc : garbage collector\n";
    $output .= "/$cmd new [x,y,z,world] > world : create portal at location\n";
    $output .= "/$cmd new world : create portal at last block location\n";
    $output .= "/$cmd delete [x,y,z,world] : delete portal entry\n";
    return $output;
  }

  public function command($cmd, $params, $issuer, $alias){
    $output = '';
    $player = $issuer instanceof Player;
    if ($player) {
      $isop = $this->api->ban->isOp($issuer->username);
      $iscr = $issuer->getGamemode() == 1;
    } else {
      $isop = false;
      $iscr = false;
    }
    if ($player && !($iscr || $isop)) return "You cannot use this command\n";

    if ($cmd == 'netherportal') {
      if (count($params) < 1) return $this->usage($cmd);
      $subcmd = strtolower(array_shift($params));
      switch ($subcmd) {
      case 'ls':
	foreach ($this->portals as $a => $b) {
	  $output .= "$a => $b\n";
	}
	break;
      case 'set':
	if (!$player) return "Can only use this command in-game\n";
	$pname = $issuer->username;
	$x = round($issuer->entity->x);
	$y = round($issuer->entity->y)-1;
	$z = round($issuer->entity->z);
	$lv = $issuer->entity->level->getName();
	$gwto = implode(' ',$params);
	if ($gwto == '') return 'No destination specified';
	if ($this->api->level->levelExists($gwto) === false) {
	  return "$gwto does not exist";
	}
	$this->portal["$x,$y,$z,$lv"] = $gwto;
	$this->api->chat->broadcast("Portal to $gwto created by $pname");
	break;
      case 'target':
	if (!$player) return "Can only use this command in-game\n";
	$pname = $issuer->username;
	$map = implode(' ',$params);
	if ($map == '') {
	  if (isset($this->targets[$pname])) {
	    $output .= "Current target is ".$this->targets[$pname]."\n";
	  } else {
	    $output .= "No target set\n";
	  }
	} elseif ($map == '-') {
	  $output .= "Target cleared\n";
	  if (isset($this->targets[$pname])) unset($this->targets[$pname]);
	} else {
	  if ($this->api->level->levelExists($map) === false) {
	    $output .= "Target $map does not exist\n";
	    if (isset($this->targets[$pname])) unset($this->targets[$pname]);
	  } else {
	    $output .= "Set portal target to $map\n";
	    $this->targets[$pname] = $map;
	  }
	}
	break;
      case 'gc':
	if ($player && !$isop) return "Not allowed\n";
	return $this->gc_maps();
	break;
      case 'new':
	if ($player && !$isop) return "Not allowed\n";
	$atob = preg_split('/\s*>\s*/',implode(' ',$params),2);
	switch (count($atob)) {
	case 0:
	  return "Nothing specified\n";
	  break;
	case 1:
	  if (is_null($this->marker)) {
	    return "Must create at least one block of the right type\n";
	  }
	  $loc = $this->marker;
	  $target = $atob[0];
	  break;
	case 2:
	  list($loc,$target) = $atob;
	  $mv = array();
	  if (!preg_match('/^(\d+),(\d+),(\d+),(.+)$/',$loc,$mv)) {
	    return "$loc does not match x,y,z,world format\n";
	  }
	  if ($this->api->level->levelExists($mv[4]) === false) {
	    return "Unknown location $loc\n";
	  }
	  break;
	}
	if ($this->api->level->levelExists($target) === false) {
	  return "Target map $target does not exist\n";
	}
	$this->portals[$loc] = $target;
	$this->config->set('portals',$this->portals);
	$this->config->save();
	$output .= "Creating a portal to $target at $loc\n";
	break;
      case 'delete':
	$portal = implode(' ',$params);
	if (isset($this->portals[$portal])) {
	  $output = "Portal at $portal deleted\n";
	  unset($this->portals[$portal]);
	  $this->config->set('portals',$this->portals);
	  $this->config->save();
	} else {
	  $output = "No portal at $portal found\n";
	}
	break;
      default:
	return $this->usage($cmd);
	break;
      }
    } else {
      $output = "$cmd: unimplemented\n";
    }
    return $output;
  }

  public function gc_maps() {
    $v = array();
    foreach ($this->portals as $a => $b) {
      if (preg_match('/^(\d+),(\d+),(\d+),(.+)$/',$a,$mv)) {
	if ($this->api->level->levelExists($mv[4]) === false) {
	  // Source map doesn't exist
	  array_push($v,$a);
	  continue;
	}
	if ($this->api->level->levelExists($b) === false) {
	  // Target map doesn't exist
	  array_push($v,$a);
	  continue;
	}
      } else {
	// Wrong format...
	array_push($v,$a);
      }
    }
    if (count($v) > 0) {
      foreach ($v as $i) {
	unset($this->portals[$i]);
      }
      $this->config->set('portals',$this->portals);
      $this->config->save();
    }
  }
  public function brkblkH(&$data,$event) {
    $target = $data['target'];
    $level = $data['player']->level;
    $loc = $target->x.",".$target->y.",".$target->z.",".$level->getName();
    console("[DEBUG] LOCATION: $loc");
    return true;
    if (isset($this->portals[$loc])) {
      unset($this->portals[$loc]);
      $this->config->set('portals',$this->portals);
      $this->config->save();
      $this->api->chat->broadcast("Portal at $loc destroyed");
    }
    return true;
  }
  public function plcblkH(&$data,$event) {
    $item = $data['item'];
    if (!$item->isPlaceable())return true;
    $hand = $item->getBlock();
    if ($hand->getID() === $this->blockID) {
      $level = $data['player']->level;
      $loc = $data['block']->x.",".$data['block']->y.",".$data['block']->z.",".$level->getName();
      if (isset($this->targets[$data['player']->username])) {
	// Target available... create portal
	$target = $this->targets[$data['player']->username];
	$this->portals[$loc] = $target;
	$this->config->set('portals',$this->portals);
	$this->config->save();
	$this->api->chat->broadcast("Portal to $target created at $loc");
      } else {
	console("[INFO] NetherPortal Marked $loc by ".$data['player']->username);
	$this->marker = $loc;
      }
    }
    return true;
  }


  public function entitymove($data){
    $x = round($data->x,0,PHP_ROUND_HALF_DOWN);
    $y = round($data->y,0,PHP_ROUND_HALF_DOWN)-1;
    $z = round($data->z,0,PHP_ROUND_HALF_DOWN);
    $level = $data->level->getName();

    $location =$x.','.$y.','.$z.','.$level;
    if (isset($this->portals[$location])) {
      console("[DEBUG] LOCATION: $location - "
	      .$data->x.",".$data->y.",".$data->z);

      $player = $data->player;
      $target_map = $this->portals[$location];
      if ($this->api->level->levelExists($target_map) === false) {
	$player->sendChat("Nothing happens ($target_map not found!)");
      } else {
	$player->sendChat("Portal activated...");
	$this->api->level->loadLevel($target_map);
	$player->teleport($this->api->level->get($target_map)->getSafeSpawn());
	$player->sendChat("Teleported to $target_map");
      }
    }
  }

  public function __destruct(){}
}
?>
