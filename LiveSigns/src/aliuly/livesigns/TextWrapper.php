<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
*/

namespace aliuly\livesigns;

abstract class TextWrapper{

	private static $characterWidths = [
		4, 2, 5, 6, 6, 6, 6, 3, 5, 5, 5, 6, 2, 6, 2, 6,
		6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 2, 2, 5, 6, 5, 6,
		7, 6, 6, 6, 6, 6, 6, 6, 6, 4, 6, 6, 6, 6, 6, 6,
		6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 4, 6, 4, 6, 6,
		6, 6, 6, 6, 6, 5, 6, 6, 2, 6, 5, 3, 6, 6, 6, 6,
		6, 6, 6, 4, 6, 6, 6, 6, 6, 6, 5, 2, 5, 7
	];

	private static $allowedChars = " !\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_abcdefghijklmnopqrstuvwxyz{|}~";

	private static $allowedCharsArray = [];

	public static function init(){
		self::$allowedCharsArray = [];
		$len = strlen(self::$allowedChars);
		for($i = 0; $i < $len; ++$i){
			self::$allowedCharsArray[self::$allowedChars{$i}] = self::$characterWidths[$i];
		}
	}


	/**
	 * @param $text
	 * @return string
	 */
	public static function wrap($text,$maxWidth=75){
		if (count(self::$allowedCharsArray) == 0) self::init();

		$result = "";
		$len = strlen($text);
		$lineWidth = 0;
		for($i = 0; $i < $len; ++$i){
			$char = $text{$i};
			if (ord($char) == 194 && ord($text{$i+1}) == 167) {
				// This is a color escape...
				$result .= $char.$text{$i+1}.$text{$i+2};
				$i += 2;
				continue;
			}

			if($char === "\n"){
				$lineWidth = 0;
			}elseif(isset(self::$allowedCharsArray[$char])){
				$width = self::$allowedCharsArray[$char];

				if($lineWidth + $width > $maxWidth){
					$result .= "\n";
					$lineWidth = 0;
				}
				$lineWidth += $width;
			}else{
				continue;
			}

			$result .= $char;
		}

		return $result;
	}
	/**
	 * @param $text
	 * @return string
	 */
	public static function wwrap($text,$maxWidth=75){
		if (count(self::$allowedCharsArray) == 0) self::init();

		$result = "";
		$len = strlen($text);
		$lineWidth = 0;
		$wordWidth = 0;
		$wordLen = 0;
		for($i = 0; $i < $len; ++$i){
			$char = $text{$i};
			if (ord($char) == 194 && ord($text{$i+1}) == 167) {
				// This is a color escape...
				$result .= $char.$text{$i+1}.$text{$i+2};
				$i += 2;
				continue;
			}

			if($char === "\n"){
				$lineWidth = 0;
				$wordWidth = 0;
				$wordLen = 0;
			}elseif(ctype_space($char) && isset(self::$allowedCharsArray[$char])){
				$wordWidth = 0;
				$wordLen = 0;
				$width = self::$allowedCharsArray[$char];
				if($lineWidth + $width > $maxWidth){
					$result .= "\n";
					$lineWidth = 0;
					continue;
				} else {
					if ($lineWidth == 0) continue;
					$lineWidth += $width;
				}
			}elseif(isset(self::$allowedCharsArray[$char])){
				$width = self::$allowedCharsArray[$char];

				if($lineWidth + $width > $maxWidth){
					if ($wordWidth >= $maxWidth || $wordWidth == 0) {
						$result .= "\n";
						$lineWidth = 0;
					} else {
						$pword = substr($result,-$wordLen);
						$result = substr($result,0,strlen($result)-$wordLen)."\n".
							$pword;
						$lineWidth = $wordWidth;
					}
				}
				$lineWidth += $width;
				$wordWidth += $width;
				$wordLen++;
			}else{
				continue;
			}

			$result .= $char;
		}

		return $result;
	}
}
