<?php
namespace aliuly\common;

/**
 * File Utilities
 */
abstract class FileUtils {
	/**
	 * Recursive copy function
	 * @param str src source path
	 * @param str dst source path
	 * @return bool
	 */
	static public function cp_r($src,$dst) {
		if (is_link($src)) {
			$l = readlink($src);
			if (!symlink($l,$dst)) return false;
		} elseif (is_dir($src)) {
			if (!mkdir($dst)) return false;
			$objects = scandir($src);
			if ($objects === false) return false;
			foreach ($objects as $file) {
				if ($file == "." || $file == "..") continue;
				if (!self::cp_r($src.DIRECTORY_SEPARATOR.$file,$dst.DIRECTORY_SEPARATOR.$file))
					return false;
			}
		} else {
			if (!copy($src,$dst)) return false;
		}
		return true;
	}
	/**
	 * Recursive delete function
	 * @param str path
	 * @return bool
	 */
	static public function rm_r($path) {
		if (!is_link($path) && is_dir($path)) {
			$objects = scandir($path);
			if ($objects === false) return false;
			foreach ($objects as $file) {
				if ($file == "." || $file == "..") continue;
				if (!self::rm_r($path.DIRECTORY_SEPARATOR.$file)) return false;
			}
			return rmdir($path);
		}
		return unlink($path);
	}
	/**
	 * Creates a temporary directory
	 * @param str $dir
	 * @param str $prefix
	 * @param int $mode
	 * @return str
	 */
	static public function tempdir($dir, $prefix='', $mode=0700) {
		if (substr($dir, -1) != '/') $dir .= '/';
		do {
			$path = $dir.$prefix.mt_rand(0, 9999999);
		} while (!mkdir($path, $mode));
		return $path;
	}

}
