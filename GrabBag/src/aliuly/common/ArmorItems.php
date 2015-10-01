<?php
namespace aliuly\common;
use pocketmine\item\Item;
//= api-features
//: - Armor constants

/**
 * Armor related values
 */
abstract class ArmorItems {
  const ERROR = -1;
  /* Armor parts */
  const HEAD = 0;
  const BODY = 1;
  const LEGS = 2;
  const BOOTS =3;
  /* Armor quality */
  const NONE = 0;
  const LEATHER = Item::LEATHER_CAP;
  const CHAINMAIL = Item::CHAIN_HELMET;
  const IRON = Item::IRON_HELMET;
  const DIAMOND = Item::DIAMOND_HELMET;
  const GOLD = Item::GOLD_HELMET;

  /**
   * Returns the armor part of a string
   * @param str $str - string to parse
   * @return int
   */
  static public function str2part($str) {
    switch(strtolower(substr($str,0,1))) {
      case "h"://ead|helmet
        return self::HEAD;
      case "c":
        switch(strtolower(substr($str,0,2))) {
          case "ca"://p
            return self::HEAD;
          case "ch"://est
            return self::BODY;
        }
        break;
      case "t"://unic
        return self::BODY;
      case "b":
        switch(strtolower(substr($str,0,3))) {
          case "bod"://y
            return self::BODY;
          case "boo"://ts
            return self::BOOTS;
        }
        break;
      case "p"://ants
      case "l"://egs
        return self::LEGS;
    }
    return self::ERROR;
  }
  /**
   * Returns the armor quality of a string
   * @param str $str - string to parse
   * @return int
   */
  static public function str2quality($str) {
    switch(strtolower(substr($str,0,1))) {
      case "l"://eather
        return self::LEATHER;
      case "c"://hainmail
        return self::CHAINMAIL;
      case "i"://ron
        return self::IRON;
      case "g"://old
        return self::GOLD;
      case "d"://iamond
        return self::DIAMOND;
      case "n"://one
        return self::NONE;
    }
    return self::ERROR;
  }
  /**
   * Returns the item ID from an armor class / armor type constants
   * @param int $q - armor quality
   * @param int $p - armor part
   * @return int
   */
   static public function getItemId($q,$p) {
     if ($p < self::HEAD || $p > self::BOOTS) return self::ERROR;
     switch ($q) {
       case self::LEATHER:
       case self::CHAINMAIL:
       case self::IRON:
       case self::GOLD:
       case self::DIAMOND:
         return $q + $p;
       case self::NONE:
        return Item::AIR;
     }
     return self::ERROR;
   }
   /**
    * Given an item ID, return the armor type constant that applies...
    * @param int $item - item id
    * @return int
    */
    static public function getArmorPart($item) {
      switch ($item) {
        case Item::LEATHER_CAP: return self::HEAD;
        case Item::LEATHER_TUNIC: return self::BODY;
        case Item::LEATHER_PANTS: return self::LEGS;
        case Item::LEATHER_BOOTS: return self::BOOTS;
        case Item::CHAIN_HELMET: return self::HEAD;
        case Item::CHAIN_CHESTPLATE: return self::BODY;
        case Item::CHAIN_LEGGINGS: return self::LEGS;
        case Item::CHAIN_BOOTS: return self::BOOTS;
        case Item::IRON_HELMET: return self::HEAD;
        case Item::IRON_CHESTPLATE: return self::BODY;
        case Item::IRON_LEGGINGS: return self::LEGS;
        case Item::IRON_BOOTS: return self::BOOTS;
        case Item::DIAMOND_HELMET: return self::HEAD;
        case Item::DIAMOND_CHESTPLATE: return self::BODY;
        case Item::DIAMOND_LEGGINGS: return self::LEGS;
        case Item::DIAMOND_BOOTS: return self::BOOTS;
        case Item::GOLD_HELMET: return self::HEAD;
        case Item::GOLD_CHESTPLATE: return self::BODY;
        case Item::GOLD_LEGGINGS: return self::LEGS;
        case Item::GOLD_BOOTS: return self::BOOTS;
        default: return self::ERROR;
      }
    }
}
