<?php
/*
__PocketMine Plugin__
name=Goto
description=Plugin for moving and teleporting
version=0.1
author=Alex
class=GotoPlugin
apiversion=9,10,11
*/

/**
 ** # META_NAME
 **
 ** * * *
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
 ** *Goto* is a Teleport plugin to move around multiple worlds on command.
 **
 ** Commands are restricted to "creative", "ops" or "console", depending
 ** on the context.
 **
 ** # Commands
 **
 ** The only command is `/go` but it accepts the following sub-commands:
 **
 ** - `/go to [world|player|chkpnt|marker]`  
 **   Alias: `goto`  
 **   Teleport to location.
 ** - `/go push [world|player|chkpnt|marker]`  
 **   Alias: `push`  
 **   Saves current location on the stack and teleports to location.
 ** - `/go pop`  
 **   Alias: `pop`  
 **   Teleports to the top location from the stack.
 ** - `/go move [player] > [world|player|chkpnt|marker]`  
 **   Alias: `move`  
 **   **OP only command**  
 **   Teleport `[player]` to location.
 ** - `/go summon [player]`  
 **   Alias: `summon`  
 **   **OP only command**  
 **   Teleports `[player]` to your location.  Original location is saved.
 ** - `/go dismiss [player]`  
 **   Alias: `dismiss`  
 **   **OP only command**  
 **   Teleports `[player]` to the location of their first summoning.
 **
 ** # Changes
 **
 ** * 0.1 : Initial version
 **
 ** # TODO
 **
 ** - Implement
 **   - checkpoint
 **   - mark
 **   - warp
 **   - rm
 **   - ls
 **
 ** # Known Issues
 **
 ** - Needs more testing...
 **
 **/

class GotoPlugin implements Plugin{
  private $api, $config, $server;
  private $stack;
  private $summon_marker;
  private $bookmarks;
  private $chkpnts;

  public function __construct(ServerAPI $api, $server = false){
    $this->api = $api;
    $this->server = ServerAPI::request();
    $this->stack = array();
    $this->summon_marker = array();
  }

  public function init(){
    $this->config = new Config($this->api->plugin->configPath($this).
			       "config.yml", CONFIG_YAML,
			       array(
				     "bookmarks" => array(),
				     ));
    $this->bookmarks = $this->config->get('bookmarks');
    $this->api->console->register('go','[subcmd] ...',array($this,'command'));
    $this->api->console->alias("goto", "go to");
    $this->api->console->alias("push", "go push");
    $this->api->console->alias("pop", "go pop");
    $this->api->console->alias("move", "go move");
    $this->api->console->alias("summon", "go summon");
    $this->api->console->alias("dismiss", "go dismiss");
    $this->api->ban->cmdWhitelist('go');
  }
  private function usage($cmd) {
    $output = '';
    $output .= "Usage: /$cmd <commmand> [parameters...]\n";
    $output .= "/$cmd to [player|world|mark]: teleport\n";
    $output .= "/$cmd push [player|world|mark]: save location and teleport\n";
    $output .= "/$cmd pop: teleport to previous location saved by push\n";
    //$output .= "/$cmd checkpoint [name]: create a temp private marker\n";
    //$output .= "/$cmd mark [name]: create a private marker\n";
    //$output .= "/$cmd warp [name] : create a public marker\n";
    $output .= "/$cmd summon [player] : move player to your location\n";
    $output .= "/$cmd dismiss [player] : send player back\n";
    $output .= "/$cmd move [player] > [world] : move [player] to [world]\n";
    //$output .= "/$cmd ls : list markers\n";
    //$output .= "/$cmd rm : remove marker\n";
    return $output;
  }

  public function getPos($player) {
    $p = $this->api->player->get($player);
    if (($p instanceof Player) && ($p->entity instanceof Entity)) {
      return array('x' => $p->entity->x,
		   'y' => $p->entity->y,
		   'z' => $p->entity->z,
		   'level' => $p->entity->level->getName());
    }
    return NULL;
  }

  public function array2Pos($place) {
    if (!$this->api->level->levelExists($place['level'])) return NULL;
    console("[DEBUG] Bookmark ".$place->x.","
	    .$place->y.",".$place->z.",".$place->level);
    return new Position($place['x'],$place['y'],$place['z'],
			$this->api->level->get($place['level']));
  }
  public function cnvPos($place,$bmdir) {
    if (is_array($place)) {
      // This is already in the right format...
      if (isset($place['x']) && isset($place['y']) && isset($place['z'])
	  && isset($place['level'])) return array2Pos($place);
      $place = implode(' ',$place);
    }
    if ($place == '') return NULL;

    console("[DEBUG] Teleporting to $place ($bmdir)");

    if (($p = $this->api->player->get($place)) != false) {
      // Go to another player
      console("[DEBUG] go to player $place");
      return Position($p->entity->x,$p->entity->y,$p->entity->z,
			      $p->entity->level);
    }
    if ($this->api->level->levelExists($place)) {
      // Go to another world
      console("[DEBUG] go to world $place");
      if ($this->api->level->loadLevel($place) === false) return NULL;
      return $this->api->level->get($place)->getSafeSpawn();
    }

    if (!is_null($bmdir)
	&& isset($this->chkpnts[$bmdir])
	&& isset($this->chkpnts[$bmdir][$place])) {
      // Goto chkpnt
      console("[DEBUG] go to chkpnt $place");
      $place = $this->chkpnts[$bmdir][$place];
    } else if (!is_null($bmdir)
	&& isset($this->bookmarks[$bmdir])
	&& isset($this->bookmarks[$bmdir][$place])) {
      console("[DEBUG] go to bmark $place");
      $place =  $this->bookmarks[$bmdir][$place];
    } else if (isset($this->bookmarks[''])
	&& isset($this->bookmarks[''][$place])) {
      console("[DEBUG] go to warp $place");
      $place = $this->bookmarks[''][$place];
    } else {
      console("[DEBUG] Not found $place");
      return NULL;
    }
    return array2Pos($place);
  }

  public function move($player,$place,$bmdir) {
    $place = $this->cnvPos($place,$bmdir);
    if (is_null($place)) return false;

    console("[DEBUG] TP $player ".$place->x.","
	    .$place->y.",".$place->z.",".$place->level->getName());

    $p = $this->api->player->get($player);
    if (($p instanceof Player) && ($p->entity instanceof Entity)) {
      return $p->teleport($place);
    }
    return false;
  }

  public function command($cmd, $params, $issuer, $alias){
    console("[DEBUG] command:$cmd\n");
    $output = '';
    $player = $issuer instanceof Player;
    if ($player) {
      $isop = $this->api->ban->isOp($issuer->username);
      $iscr = $issuer->getGamemode() == 1;
    } else {
      $isop = false;
      $iscr = false;
    }
    console("[DEBUG] player=$player iscr=$iscr isop=$isop");
    if ($player && !($iscr || $isop)) return "You cannot use this command";

    if ($cmd != 'go') return 'Unimplemented command';

    if (count($params) < 1) return $this->usage($cmd);
    $subcmd = strtolower(array_shift($params));
    console("[DEBUG] subcommand:$subcmd\n");
    switch ($subcmd) {
    case 'to':
      if (!$player) return "You can only use this command in-game";
      if ($this->move($issuer->username,$params,$issuer->username) === false) {
	$output.="Failed to teleport";
      } else {
	$output.="Teleported!";
      }
      break;
    case 'push':
      if (!$player) return "You can only use this command in-game";
      $myname = $issuer->username;
      if (!isset($this->stack[$myname])) $this->stack[$myname] = array();
      $pos = $this->getPos($myname);
      if (is_null($pos)) return "Unable to determine current location";
      if ($this->move($myname,$params,$myname) === false) {
	$output.="Failed to teleport";
      } else {
	$output.="Teleported!";
	array_push($this->stack[$myname],$this->getPos($myname));
      }
      break;
    case 'pop':
      if (!$player) return "You can only use this command in-game";
      $myname = $issuer->username;
      if (isset($this->stack[$myname]) && count($this->stack[$myname] > 0)) {
	$pos = array_pop($this->stack[$myname]);
	if ($this->move($myname,$pos,$myname) === false) {
	  $output.="Failed to teleport";
	} else {
	  $output.="Teleported!";
	}
      } else {
	return "Stack empty";
      }
      break;
    case 'move':
      if ($player && !$isop) return "You can not use this command";
      $params = preg_split('/\s*>\s*/',implode(' ',$params),2);
      if (count($params) != 2) return $this->usage($cmd);
      list($victim,$target) = $params;
      if ($player) {
	$myname = $issuer->username;
      } else {
	$myname = NULL;
      }
      if ($this->move($victim,$target,$myname) == false) {
	$output .= "Unable to teleport $victim.\n";
      } else {
	$output .= "Teleported $victim to $target.\n";
      }
      break;
    case 'summon':
      if (!$player) return "You can only use this command in-game";
      if (!$isop) return "You can not use this command";
      $myname = $issuer->username;
      $victim = impode(' ',$params);
      $pos = $this->getPos($victim);
      if (is_null($pos)) return "Unable to summon $victim";
      if ($this->move($victim,$myname,NULL) === false) {
	$output .= "Failed to summon $victim";
      } else {
	$output .= "Say hi to $victim";
	if (!isset($this->summon_marker[$victim])) {
	  $this->summon_marker[$victim] = $pos;
	}
      }
      break;
    case 'dismiss':
      if (!$player) return "You can only use this command in-game";
      if (!$isop) return "You can not use this command";
      $myname = $issuer->username;
      $victim = impode(' ',$params);
      if (!isset($this->summon_marker[$victim])) {
	return "Don't know where to dismiss $victim to";
      }
      $pos = $this->summon_marker[$victim];
      unset($this->summon_marker[$victim]);
      if ($this->move($victim,$pos,NULL) === false) {
	$output .= "Failed to dismiss $victim";
      } else {
	$output .= "Teleported $victim";
      }
      break;
    default:
      return "Unknown sub command $subcmd\n".$this->usage($cmd);
      break;
    }
    return $output;
  }

  public function __destruct(){}
}
?>
