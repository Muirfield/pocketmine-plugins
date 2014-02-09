<?php
/*
__PocketMine Plugin__
class=InfoEssentials
apiversion=10,11
version=Gamma 3.1415926
name=InfoEssentials
author=PEMapModder - Hacked version by Alex
*/
/*
Gamma update summary:
3			=> initial commit of gamma
3.1			=> added /setarmor
3.1.4		=> optimized the evaluation and interpreting of item strings
===RELEASE ON POCKETMINE FORUMS===
3.141		=> added permissions config file
3.1415		=> corrected the misspelled PermissionPlus (no 's')
3.1415.1	=> fixed crashing if PermissionPlus is installed
3.14159		=> added /rmarmor
3.141592	=> colourful update and op list

4.0 => Hacked version
*/
class InfoEssentials implements Plugin{
	private $c,$s;
	public function __construct(ServerAPI $a,$s=0){}
	public function __destruct(){}
	public function init(){
		$this->s=ServerAPI::request();
		$s=$this->s;
		$api=$this->s->api;
		$b=ServerAPI::request()->api->ban;
		$c=ServerAPI::request()->api->console;
		$this->c=$c;

		$c->register("getping","[IGN] get ping of a player",array($this,"getPing"));
		$c->register("seearmor","[IGN] see if a player is sleeping",array($this,"seeArmor"));
		$c->register("seegm","[IGN] see the gamemode of a player",array($this,"seeGm"));
		$c->register("getpos","[IGN] get the position of a player",array($this,"getPos"));
		$c->register("pw","ping warn",array($this,"pingWarn"));
		$c->register("setarmor","<material> <part> [IGN] set a player's armor",array($this,"setArmor"));
		$c->register("rmarmor","<part> [IGN] set part of a player's armor to empty",array($this, "rmArmor"));
		$c->register("seeop","see op list",array($this,"seeOp"));
		$this->s->addHandler("player.spawn",array($this,"entered"));
		$path=$api->plugin->configPath($this)."settings.";
		if(file_exists($path."txt"))$path.="txt";
		else $path.="yml";
		$settings=new Config($path,CONFIG_YAML,array("non-op permissions"=>array(
				"/getping"=>true,
				"/seearmor"=>true,
				"/seegm"=>true,
				"/getpos"=>true,
				"/pw"=>false,
				"/setarmor"=>false,
				"/rmarmor"=>false,
				"/seeop"=>true
			)));
		if(!($settings instanceof Config))return;
		foreach($settings->get("non-op permissions") as $cmd=>$boolean){
				if($boolean)
					$b->cmdWhitelist(substr($cmd,1));
		}
	}
	public function pingWarn($c,$a,$p){
		foreach($this->s->api->player->getAll() as $p){
			if(isset($p->ping)){
				console("$p ping is {$p->ping} ms.");
				if($p->ping>1000){
					$p->sendChat("Your ping is too high!");
					$p->sendChat("Having a ping of {$p->ping} ms is a poor connection!");
				}
			}
		}
	}
	public function entered($d,$e){console("$d entered");$this->c->run("pw");}
	public function getPing($c,$a,$p){
		$this->c->run("pw");
		if(!isset($a[0])){
			if(!($p instanceof Player))
				return "Usage: /getping [IGN]";
		}
		else $p=ServerAPI::request()->api->player->get($a[0]);
		if($p===false)return "Player not found";
		return "{$p}'s ping: ".FORMAT_AQUA.$p->getLag().FORMAT_RESET." packet loss: {$p->getPacketLoss()} bandwidth: {$p->getBandwidth()}\n";
	}
	public function seeArmor($c,$a,$p){
		$this->c->run("pw");
		if(!isset($a[0])){
			if(!($p instanceof Player))
				return "Usage: /seearmor [IGN]";
		}
		else $p=ServerAPI::request()->api->player->get($a[0]);
		if($p===false)return "Player not found";
		return "\n $p's armor: \n".
			"Helmet:     ".$this->evalArmor($p->getArmor(0)->getID(),$p)."\n".
			"Chestplate: ".$this->evalArmor($p->getArmor(1)->getID(),$p)."\n".
			"Leggings:   ".$this->evalArmor($p->getArmor(2)->getID(),$p)."\n".
			"Boots:      ".$this->evalArmor($p->getArmor(3)->getID(),$p);
	}
	public function seeGm($c,$a,$p){
		$this->c->run("pw");
		if(!isset($a[0])){
			if(!($p instanceof Player))
				return "Usage: /seegm [IGN]";
		}
		else $p=ServerAPI::request()->api->player->get($a[0]);
		if($p===false)return "Player not found";
		return "$p is in ".($p->getGamemode()=="creative"?FORMAT_YELLOW:FORMAT_AQUA)."{$p->getGamemode()} mode.\n";
	}
	public function getPos($c,$a,$p){
		$this->c->run("pw");
		if(!isset($a[0])){
			if(!($p instanceof Player))
				return "Usage: /getpos [IGN]";
			$i=$p;
		}
		else $i=ServerAPI::request()->api->player->get($a[0]);
		if($i===false)return "Player not found";
		$p=$i->entity;
		return "$i is in world \"".$p->level->getName()."\" at (X,Y,Z) (".FORMAT_RED.$p->x.",".FORMAT_YELLOW.$p->y.",".FORMAT_AQUA.$p->z.").\n".FORMAT_RESET;
	}
	public function setArmor($c,$a,$p){
		$this->c->run("pw");
		if(!isset($a[2])){
			if(!($p instanceof Player))
				return "Usage: /setarmor <material> <part> [IGN]";
		}
		else $p=ServerAPI::request()->api->player->get($a[2]);
		if($p===false)return "Player not found";
		if($this->interpretMaterial($a[0])===false or $this->interpretPart($a[1])===false)
			return "Usage: /setarmor <material> <part> [IGN]";
		$p->setArmor($this->interpretPart($a[1]), BlockAPI::getItem($this->interpretMaterial($a[0])+$this->interpretPart($a[1])));
		return "$p's armor is set\n";
	}
	public function rmArmor($c,$a,$p){
		$this->c->run("pw");
		if(!isset($a[1])){
			if(!($p instanceof Player))
				return "Usage: /rmarmor <part> [IGN]";
		}
		else $p=ServerAPI::request()->api->player->get($a[1]);
		if($p===false)return "Player not found";
		if($this->interpretPart($a[0])===false)
			return "Usage: /rmarmor <part> [IGN]";
		$p->setArmor($this->interpretPart($a[0]), BlockAPI::getItem(0));
		return "Removed $p's part of armor\n";
	}
	public function seeOp($c,$a){
		$list=explode("\n",file_get_contents(FILE_PATH."ops.txt"));
		if(!isset($a[0]))$a[0]=1;
		if(!is_numeric($a[0]))return "Usage: /seeop [page]";
		else $n=((int)$a[0])-1;
		$out="Op list: Page ".($n+1)." of ".(ceil(count($list)/5))."\n";
		for($i=0;$i<5;$i++){
			$out.=($list[$n*5+$i]."\n");
		}
		return $out;
	}
	protected function interpretMaterial($str){
		switch(strtolower($str)){
			case "diamond":return 310;
			case "gold":case "budder":case "butter":case "golden":return 314; 
			case "leather":return 298;
			case "iron":return 306;
			case "chain":return 302;
			case "0":case "null":case "air":case "empty":case "off":case "nothing":return 0;
			default:return false;
		}
	}
	protected function interpretPart($str){
		$str=strtolower(str_replace("s","",$str));
		switch($str){
			case "1":case "helm":case "helmet":
				return 0;
			case "2":case "tunic":case "chest":case "chestplate":case "cp":case "shirt":
				return 1;
			case "3":case "legging":case "pant":case "trouser":
				return 2;
			case "4":case "boot":case "shoe":case "sneaker":
				return 3;
			default:
				return false;
		}
	}
	protected function evalArmor($id,$p="Unknown"){
		if($id==0)return "Empty";
		switch(round($id/4)){
			default:$o="unknown-material";console(FORMAT_YELLOW."[WARNING] [InfoEss] Something strange happened. $p has an armor id of $id");break;
			case 75:$o=FORMAT_RED."leather".FORMAT_RESET;break;
			case 76:$o=FORMAT_AQUA."chain".FORMAT_RESET;break;
			case 77:$o=FORMAT_AQUA."iron".FORMAT_RESET;break;
			case 78:$o=FORMAT_AQUA."diamond".FORMAT_RESET;break;
			case 79:$o=FORMAT_YELLOW."golden".FORMAT_RESET;break;
		}
		switch($id%4){
			case 2:return "$o helmet";
			case 3:return "$o chestplate";
			case 0:return "$o leggings";
			case 1:return "$o boots";
		}
		console(FORMAT_RED."[ERROR] [InfoEss] InfoEssentials has an unexpected behaviour of itself. Please send this report to @PEMapModder on PocketMine Forums:\n".
			date("d-n-y G:i:s")." $id is attempted to be evaluated for armor type. Data: ".((isset($p->entity) and ($p instanceof Player))?$p->getArmor(0)."\n".$p->getArmor(1)."\n".$p->getArmor(2)."\n".$p->getArmor(3):"Unknown"));
	}

}
