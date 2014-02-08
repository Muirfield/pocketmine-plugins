<?php

/* 
__PocketMine Plugin__ 
name=SignPortal
description=Multiworld portal plugin
version=1.0.2
author=99leonchang
class=SignPortal
apiversion=10,11
*/

class SignPortal implements Plugin{
    private $api, $server, $path;
    public function __construct(ServerAPI $api, $server = false){
        $this->api = $api;
        $this->server = ServerAPI::request();
    }

    public function init(){
        $this->api->addHandler("player.block.touch", array($this, "eventHandler"));
        $this->api->addHandler("tile.update", array($this, "eventHandler"));
        $this->config = new Config($this->api->plugin->configPath($this)."config.yml", CONFIG_YAML, array(
            ));

    }
    public function __destruct() {}

    public function eventHandler(&$data, $event){
        switch ($event) {
            case "tile.update":
                if ($data->class === TILE_SIGN) {
                    $usrname = $data->data['creator'];
                    if ($data->data['Text1'] == "[WORLD]"){
                            $mapname = $data->data['Text2'];
                            if ($this->api->level->loadLevel($mapname) === false) {
                                $data->data['Text1'] = "[BROKEN]";
                                $this->api->chat->sendTo(false, "[SignPortal] World $mapname not found!", $usrname);
                                return false;
                            }
                            return true;
                        }
                    }
            break;
            case "player.block.touch":
                        $tile = $this->api->tile->get(new Position($data['target']->x, $data['target']->y, $data['target']->z, $data['target']->level));
                        if ($tile === false) break;
                        $class = $tile->class;
                        switch ($class) {
                            case TILE_SIGN:
                                switch ($data['type']) {
                                    case "place":
                                        if ($tile->data['Text1'] == "[WORLD]") {
                                            $mapname = $tile->data['Text2'];
                                            if ($this->api->level->loadLevel($mapname) === false) {
                                                $data->sendChat("[SignPortal] World $mapname not found");
                                            }
                                            else {
                                                $this->api->level->loadLevel($mapname);
                                                $data["player"]->teleport($this->api->level->get($mapname)->getSpawn());
                                            }
                                        }
                                        break;
                                }
                                break;
                        }
                        break;
        }

}
}
