<?php
/*
__PocketMine Plugin__
name=NetherPortal
description=Stand on Nether Reactor to teleport
version=0.1
author=Alex
class=NetherPortal
apiversion=9,10,11
*/

/**
 ** # NetherPortal Plugin
 **
 **   META_NAME META_VERSION
 **   Copyright (C) 2013 Alejandro Liu  
 **   All Rights Reserved.
 **
 **   This program is free software: you can redistribute it and/or modify
 **   it under the terms of the GNU General Public License as published by
 **   the Free Software Foundation, either version 2 of the License, or
 **   (at your option) any later version.
 **
 **   This program is distributed in the hope that it will be useful,
 **   but WITHOUT ANY WARRANTY; without even the implied warranty of
 **   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 **   GNU General Public License for more details.
 **
 **   You should have received a copy of the GNU General Public License
 **   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 **
 ** This is the first version of my NetherPortal plugin
 **
 ** # Changes
 **
 ** * 0.1 : Initial Release
 **
 ** # TODO
 **
 ** # Known Issues
 **
 **/

class NetherPortal implements Plugin{
  private $api, $config, $server;
  private $blockID;
  private $lastPortal;
  private $locations;

  public function __construct(ServerAPI $api, $server = false){
    $this->api = $api;
    $this->server = ServerAPI::request();
  }

  public function init(){
    $this->config = new Config($this->api->plugin->configPath($this).
			       "config.yml", CONFIG_YAML,
			       array(
				     "locations" => array(),
				     'ItemID' => '247',
				     ));
    // 247 = Nether Reactor Core
    $this->blockID = $this->config->get('ItemID');
    $this->lastPortal = NULL;
    $this->locations = $this->config->get('locations');
    $this->api->event("entity.move", array($this, "entitymove"));
    $this->api->console->register("nportal", "[world]",array($this, "command"));
  }
  public function command($cmd, $params, $issuer, $alias){
    $output = "";
    switch($cmd) {
    case 'nportal':
      if ($issuer instanceof Player){
	if ($issuer->getGamemode() != 1
	    && !$this->api->ban->isOp($issuer->username)) {
	  $output .= "You are not allowed to use this command.\n";
	  $ouptut .= "Use [list] instead\n";
	  break;
	}
      }

      if (is_null($this->lastPortal)) {
	$output .= "No recent portals triggered\n";
	break;
      }
      $target_map = implode(' ',$params);
      if ($this->api->level->loadLevel($target_map) === false) {
	$output .= "World \"$target_map\" does not exist.";
	break;
      }
      $source = implode(',',$this->lastPortal);
      $this->locations[$source] = $target_map;
      $output .= "Portal to $target_map created at $source";

      $this->config->set('locations',$this->locations);
      $this->config->save();

      break;
    default:
      $output = "Unimplemented command\n";
      break;
    }
    return $output;
  }

  public function entitymove($data){
    $block = $data->level->getBlock(new Vector3($data->x, ($data->y -1), $data->z));
    if($block->getID() == $this->blockID){
      $player = $data->player;

      $x = round($data->x,0,PHP_ROUND_HALF_DOWN);
      $y = round($data->y,0,PHP_ROUND_HALF_DOWN);
      $z = round($data->z,0,PHP_ROUND_HALF_DOWN);
      $level = $player->level->getName();

      $this->lastPortal= array($x,$y,$z,$level);
      $target_map = implode(',',$this->lastPortal);
      if (isset($this->locations[$target_map])) {
	$target_map = $this->locations[$target_map];
	if ($this->api->level->loadLevel($target_map) === false) {
	  $player->sendChat("Nothing happens ($target_map not found!)");
	} else {
	  $player->sendChat("Portal activated...");
	  $this->api->level->loadLevel($target_map);
	  $player->teleport($this->api->level->get($target_map)->getSpawn());
	  $player->sendChat("Teleported to $target_map");
	}
      } else {
	console("[INFO] NetherPortal: $x,$y,$z,$level");
      }
    }
  }

  public function __destruct(){}
}
?>
