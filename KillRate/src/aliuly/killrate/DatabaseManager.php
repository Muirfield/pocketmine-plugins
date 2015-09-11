<?php
namespace aliuly\killrate;
use pocketmine\plugin\PluginBase;

interface DatabaseManager {
	public function __construct(PluginBase $owner,$cfg);
	public function getTops($limit,$players,$scores);
	public function getScores($player);
	public function getScore($player,$type);
	public function insertScore($player,$type,$cnt);
	public function updateScore($player,$type,$cnt);
	public function delScore($player,$type = null);
	public function close();
}
