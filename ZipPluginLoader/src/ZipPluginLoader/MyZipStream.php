<?php
namespace ZipPluginLoader;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginLoadOrder;

class MyZipStream {
	// This is needed to work around bugs/incomplete features of the
	// built-in PHP Zip wrapper
	var $fp;
	var $path;
	public function stream_open($path,$mode,$opts,&$opened_path) {
		$this->path = $path;
		$zippath = preg_replace('/^myzip:/','zip:',$path);
		$this->fp = @fopen($zippath,$mode);
		if ($this->fp == false) return false;
		return true;
	}
	public function stream_close() {
		fclose($this->fp);
	}
	public function stream_read($count) {
		return fread($this->fp,$count);
	}
	public function stream_eof() {
		return feof($this->fp);
	}
	public function url_stat($path,$flags) {
		$ret = [];
		$zippath = preg_replace('/^myzip:\/\//',"",$path);
		$parts = explode('#',$zippath,2);
		if (count($parts)!=2) return false;
		list($zippath,$subfile) = $parts;
		$za = new \ZipArchive();
		if ($za->open($zippath) !== true) return false;
		$i = $za->locateName($subfile);
		if ($i === false) return false;
		$zst = $za->statIndex($i);
		$za->close();
		unset($za);
		foreach([7=>'size', 8=>'mtime',9=>'mtime',10=>'mtime'] as $a=>$b) {
			if (!isset($zst[$b])) continue;
			$ret[$a] = $zst[$b];
		}
		return $ret;
	}
	public function stream_stat() {
		return $this->url_stat($this->path,0);
	}
}
