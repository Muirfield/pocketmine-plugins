<?php
namespace ZipPluginLoader;
use pocketmine\event\plugin\PluginDisableEvent;
use pocketmine\event\plugin\PluginEnableEvent;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginDescription;
use pocketmine\plugin\PluginLoader;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\utils\PluginException;

class ZipPluginLoader implements PluginLoader{
	const PREFIX = "myzip://";
	const PLUGIN_YML = "plugin.yml";
	const ZIP_EXT = ".zip";
	const CANARY = "#multi-loader.zip";

	/** @var Server */
	private $server;

	/**
	 * @param Server $server
	 */
	public function __construct(Server $server){
		$this->server = $server;
	}

	/**
	 * Gets the PluginDescription from the file
	 *
	 * @param string $file
	 *
	 * @return PluginDescription
	 */
	public function getPluginDescription($file){//@API
		if (substr($file,0,strlen(self::PREFIX)) == self::PREFIX) {
			if (substr($file,-strlen(self::CANARY)) == self::CANARY) {
				// This is an internal path
				$file = substr($file,0,strlen($file)-strlen(self::CANARY));
			}
			return $this->myGetPluginDesc($file);
		}
		$ymls = $this->findFiles($file,"plugin.yml");
		if ($ymls === null) return null;
		if (count($ymls) > 1) {
			$plugins = $this->check_plugins($file,$ymls);
			return $this->getDummyDesc($plugins,$file);
		}
		return $this->myGetPluginDesc(self::PREFIX.$file."#".$ymls[0]);
	}

	/**
	 * Loads the plugin contained in $file
	 *
	 * @param string $file
	 *
	 * @return Plugin
	 */
	public function loadPlugin($file){//@API
		if (substr($file,0,strlen(self::PREFIX)) == self::PREFIX) {
			if (substr($file,-strlen(self::CANARY)) == self::CANARY) {
				// This is an internal path
				$file = substr($file,0,strlen($file)-strlen(self::CANARY));
			}
			$desc = $this->myGetPluginDesc($file);
			$dataFolder=$this->zipdir($file).DIRECTORY_SEPARATOR.$desc->getName();
			$this->server->getLogger()->info(TextFormat::AQUA."Loading zip NESTED plugin " . $desc->getFullName());
			return $this->initPlugin($desc,$dataFolder,$file);
		}
		$ymls = $this->findFiles($file,"plugin.yml");
		if ($ymls === null) {
			$this->server->getLogger()->error(TextFormat::RED."Unable to load zip $file");
			$this->server->getLogger()->error(TextFormat::RED."plugin.yml not found");
			throw new PluginException("Couldn't load plugin");
			return null;
		}
		if (count($ymls) > 1) {
			// Load all the internal plugins
			$plugins = $this->check_plugins($file,$ymls);
			$this->server->getLogger()->info(TextFormat::AQUA."Loading ".
														count($plugins)." plugin(s) from ".
														basename($file));
			// Check if we need to do a loadbefore...
			foreach (array_keys($plugins) as $p) {
				if (isset($plugins[$p]["loadbefore"])) {
					foreach ($plugins[$p]["loadbefore"] as $b) {
						if (isset($plugins[$b])) {
							if (isset($plugins[$b]["softdepend"])) {
								$plugins[$b]["softdepend"][] = $p;
							} else {
								$plugins[$b]["softdepend"] = [$p];
							}
						}
					}
				}
			}

			$loaded = [];
			while (count($plugins)) {
				$cnt = 0;
				foreach (array_keys($plugins) as $pname) {
					$load = true;
					// Check dependancies...
					if(isset($plugins[$pname]["depend"])) {
						foreach($plugins[$p]["depend"] as $d) {
							if (isset($plugins[$d])) {
								$load = false;
								break;
							}
							if (isset($loaded[$d])) continue;

							$found = $this->server->getPluginManager()->getPlugin($d);
							if ($found === null) {
								throw new PluginException("Missing dependancy: $d");
								return null;
							}
						}
						if (!$load) continue;
					}
					if(isset($plugins[$pname]["softdepend"])) {
						foreach($plugins[$p]["softdepend"] as $d) {
							if (isset($plugins[$d])) {
								$load = false;
								break;
							}
						}
					}
					if (!$load) continue;

					// We can load this plugin...
					$dat = $plugins[$pname];
					unset($plugins[$pname]);
					$this->server->getPluginManager()->loadPlugin($dat["path"].self::CANARY,[$this]);
					$loaded[$pname] = $dat;
					++$cnt;
				}
				if ($cnt == 0) {
					throw new PluginException("Error loading plugins");
					break;
				}
			}
			if (count($plugins)) {
				$this->server->getLogger()->error(TextFormat::RED."Failed to load plugins ".implode(", ",array_keys($plugins)));
				return null;
			}

			// Load dummy
			$plugins = $this->check_plugins($file,$ymls);
			$desc =  $this->getDummyDesc($plugins,$file);
			$dataFolder = dirname($file) . DIRECTORY_SEPARATOR . $desc->getName();
			return $this->initPlugin($desc,$dataFolder,$file);
		}
		$desc = $this->myGetPluginDesc(self::PREFIX.$file."#".$ymls[0]);
		$dataFolder = dirname($file) . DIRECTORY_SEPARATOR . $desc->getName();
		$basepath = $ymls[0] == self::PLUGIN_YML ?
					 self::PREFIX.$file."#" :
					 self::PREFIX.$file."#".dirname($ymls[0])."/";

		$this->server->getLogger()->info(TextFormat::AQUA."Loading zip plugin " . $desc->getFullName());
		return $this->initPlugin($desc,$dataFolder,$basepath);
	}
	/**
	 * Returns the filename patterns that this loader accepts
	 *
	 * @return array
	 */
	public function getPluginFilters(){//@API
		return "/\\.zip$/i";
	}
	/**
	 * @param Plugin $plugin
	 */
	public function enablePlugin(Plugin $plugin){//@API
		if($plugin instanceof PluginBase and !$plugin->isEnabled()){
			$this->server->getLogger()->info("Enabling " . $plugin->getDescription()->getFullName());

			$plugin->setEnabled(true);

			Server::getInstance()->getPluginManager()->callEvent(new PluginEnableEvent($plugin));
		}
	}

	/**
	 * @param Plugin $plugin
	 */
	public function disablePlugin(Plugin $plugin){//@API
		if($plugin instanceof PluginBase and $plugin->isEnabled()){
			$this->server->getLogger()->info("Disabling " . $plugin->getDescription()->getFullName());

			Server::getInstance()->getPluginManager()->callEvent(new PluginDisableEvent($plugin));

			$plugin->setEnabled(false);
		}
	}


	/********************************************************************/
	protected function getDummyDesc($plugins,$file) {
		$name = preg_replace('/\.zip$/i',"",basename($file));
		$ch = [
			"name" => "_". $name,
			"version" => "zipFile",
			"main" => "ZipPluginLoader\\Dummy",
			"description" => "Plugin Wrapper for loading ".$name,
		];
		foreach (["api","authors"] as $key) {
			$ch[$key] = [];
			foreach ($plugins as $pp) {
				if (!isset($pp[$key])) continue;
				foreach ($pp[$key] as $a) {
					if (isset($ch[$key][$a])) continue;
					$ch[$key][$a] = $a;
				}
			}
			$ch[$key] = array_values($ch[$key]);
		}
		foreach (["depend","softdepend","loadbefore"] as $key) {
			$ch[$key] = [];
			foreach ($plugins as $pp) {
				if (!isset($pp[$key])) continue;
				foreach ($pp[$key] as $a) {
					if (isset($plugins[$a])) continue; // Internal depedency
					if (isset($ch[$key][$a])) continue;
					$ch[$key][$a] = $a;
				}
			}
			$ch[$key] = array_values($ch[$key]);
		}
		return new PluginDescription(yaml_emit($ch,YAML_UTF8_ENCODING));
	}
	protected function myGetPluginDesc($file) {
		if (substr($file,0,strlen(self::PREFIX)) != self::PREFIX) {
			$file = self::PREFIX . $file;
		}
		if (substr($file,-strlen(self::PLUGIN_YML)) != self::PLUGIN_YML) {
			if (substr($file,-strlen(self::ZIP_EXT)) == self::ZIP_EXT) {
				$file .= "#".self::PLUGIN_YML;
			} else {
				switch(substr($file,-1)) {
					case "/":
					case "#":
						break;
					default:
						$file .= "/";
				}
				$file .= self::PLUGIN_YML;
			}
		}
		$yaml = @file_get_contents($file);
		if ($yaml == "") return null;
		return new PluginDescription($yaml);
	}
	protected function check_plugins($file,$ymls) {
		$plugins = [];

		// Check if there is a control file
		$ok = false;
		$ctl = preg_replace('/\.zip$/i','.ctl',$file);
		if (file_exists($ctl)) {
			$ctl = file($ctl,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
			$ok = [];
			foreach($ctl as $i) {
				$i = trim($i);
				if (substr($i,0,1) == ";" || substr($i,0,1) == "#") continue;
				$ok[$i] = $i;
			}
		}

		foreach ($ymls as $plugin_yml) {
			$dat = @file_get_contents(self::PREFIX.$file."#".$plugin_yml);
			if ($dat == "") continue;
			$dat = yaml_parse($dat);
			$plugin = [];
			foreach (["name","version","main"] as $str) {
				if (!isset($dat[$str])) {
					throw new PluginException("Invalid $plugin_yml");
					return null;
				}
				$plugin[$str] = $dat[$str];
			}
			if ($ok) {
				// Filter out plugins not listed in control file
				if (!isset($ok[$dat["name"]])) continue;
			}
			if (!isset($dat["api"])) {
				throw new PluginException("No API defined in $plugin_yml");
				return null;
			}
			$plugin["api"] = is_array($dat["api"]) ? $dat["api"] : [$dat["api"]];
			$plugin["path"] = self::PREFIX.$file."#".
								 ($plugin_yml == self::PLUGIN_YML ? "" :
								  dirname($plugin_yml)."/");
			foreach(["website","description","prefix","load"] as $str) {
				if (isset($dat[$str])) $plugin[$str] = $dat[$str];
			}
			$plugin["authors"] = [];
			if (isset($dat["author"])) $plugin["authors"][] = $dat["author"];
			if (isset($dat["authors"])) {
				foreach($dat["authors"] as $a) {
					$plugin["authors"][] = $a;
				}
			}
			foreach(["depend","loadBefore","softdepend"] as $arr) {
				$plugin[$arr] = isset($dat[$arr]) ? (array)$dat[$arr] : [];
			}
			foreach(["commands","permissions"] as $arr) {
				if (isset($dat[$arr]) && is_array($dat[$arr])) {
					$plugin[$arr] = $dat[$arr];
				}
			}
			$plugins[$plugin["name"]] = $plugin;
		}
		return $plugins;
	}
	protected function findFiles($zip,$file) {
		$files = [];
		$za = new \ZipArchive();
		if($za->open($zip) !== true) return null;
		// Look for plugin data...
		$basepath = null;

		for ($i=0;$i < $za->numFiles;$i++) {
			$st = $za->statIndex($i);
			if (!isset($st["name"])) continue;
			if (basename($st["name"]) == $file) {
				$files[] = $st["name"];
			}
		}
		$za->close();
		unset($za);
		if (count($files)) return $files;
		return null;
	}

	protected function initPlugin($desc,$dataFolder,$path) {
		if (!($desc instanceof PluginDescription)) {
			throw new PluginException("Couldn't load plugin");
			return null;
		}
		if(file_exists($dataFolder) and !is_dir($dataFolder)){
			throw new PluginException("Projected dataFolder '" . $dataFolder . "' for " . $descr->getName() . " exists and is not a directory");
			return null;
		}
		$className = $desc->getMain();

		$this->server->getLoader()->addPath($path . "src");
		if(!class_exists($className, true)){
			throw new PluginException("Couldn't load zip plugin " . $descr->getName() . ": main class not found");
			return null;
		}
		$plugin = new $className();
		$plugin->init($this, $this->server, $desc, $dataFolder, $path);
		$plugin->onLoad();
		return $plugin;
	}
	protected function zipdir($ff) {
		if (substr($ff,0,strlen(self::PREFIX)) == self::PREFIX) {
			$ff = substr($ff,strlen(self::PREFIX));
		}
		$p = strpos($ff,"#");
		if ($p !== false) {
			$ff = substr($ff,0,$p);
		}
		return dirname($ff);
	}
}
