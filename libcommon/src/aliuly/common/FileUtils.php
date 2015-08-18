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
			$objects = scandir($src);
			if ($objects === false) return false;
			foreach ($objects as $file) {
				if ($file == "." || $file == "..") continue;
				if (!cp_r($src.DIRECTORY_SEPARATOR.$file,$dst.DIRECTORY_SEPARATOR.$file))
					return false;
			}
		} else {
			if (!copy($src,$dst)) return false;
		}
		return false;
	}
	/**
	 * Recursive delete function
	 * @param str path
	 * @return bool
	 */
	static public function rm_r($path) {
		if (!is_link($path) && is_dir($path)) {
			$objects = scandir($src);
			if ($objects === false) return false;
			foreach ($objects as $file) {
				if ($file == "." || $file == "..") continue;
				if (!rm_r($path.DIRECTORY_SEPARATOR.$file)) return false;
			}
			return rmdir($path);
		}
		return unlink($path);
	}
}
