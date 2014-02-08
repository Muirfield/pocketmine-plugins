<?php

/* 
   __PocketMine Plugin__ 
   name=SignPortal
   description=Multiworld portal plugin (HACKED by Alex)
   version=1.0.2p1
   author=99leonchang (Hacked by Alex)
   class=SignPortal
   apiversion=10,11
*/

class SignPortal implements Plugin{
  private $api, $server, $path;
  private $text;

  public function __construct(ServerAPI $api, $server = false){
    $this->api = $api;
    $this->server = ServerAPI::request();
    $this->text = NULL;
  }

    public function init(){
        $this->api->addHandler("player.block.touch", array($this, "eventHandler"));
        $this->api->addHandler("tile.update", array($this, "eventHandler"));
	$this->api->console->register("sign", "Text1/Text2/Text3/Text4", array($this, "command"));
    }

    public function __destruct() {}

    public function eventHandler(&$data, $event){
        switch ($event) {
            case "tile.update":
                if ($data->class === TILE_SIGN) {
                    $usrname = $data->data['creator'];

		    if (!is_null($this->text)
			&& $data->data['Text1'] == ''
			&& $data->data['Text2'] == ''
			&& $data->data['Text3'] == ''
			&& $data->data['Text4'] == '') {
		      $data->data['Text1'] = $this->text['Text1'];
		      $data->data['Text2'] = $this->text['Text2'];
		      $data->data['Text3'] = $this->text['Text3'];
		      $data->data['Text4'] = $this->text['Text4'];
		    }

                    if ($data->data['Text1'] == "[WORLD]"){
                            $mapname = $data->data['Text2'];
                            if ($this->api->level->loadLevel($mapname) === false) {
                                $data->data['Text1'] = "[BROKEN]";
                                $this->api->chat->sendTo(false, "[SignPortal] World $mapname not found!", $usrname);
                                return false;
                            }
			    $this->api->chat->broadcast("Portal to $mapname created by $usrname");

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
					      $data['player']->sendChat("Portal activated...");
                                                $this->api->level->loadLevel($mapname);
                                                $data["player"]->teleport($this->api->level->get($mapname)->getSafeSpawn());
						$data['player']->sendChat("Teleported to $mapname");
                                            }
                                        }
                                        break;
                                }
                                break;
                        }
                        break;
        }

    }

    public function command($cmd, $params, $issuer, $alias){
      $output = "";
      switch($cmd) {
      case 'sign':
	$output = "OK\n";
	$newtext = implode(' ',$params);
	if ($newtext == '') {
	  $this->text = NULL;
	} else {
	  $this->text = array('Text1'=>'','Text2'=>'','Text3'=>'','Text4'=>'');
	  $i = 1;
	  foreach (preg_split('/\s*\/\s*/',$newtext) as $ln) {
	    $this->text['Text'.$i] = $ln;
	    ++$i; if ($i > 4) break;
	  }
	}
	break;
      default:
	$output = "Unimplemented command\n";
	break;
      }
      return $output;
    }


}
