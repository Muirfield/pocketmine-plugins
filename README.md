pocketmine-plugins
==================

Repository for my PocketMine plugins

## Available Plugins

* NotSoFlat - *Outdated*!
* ImportMap - Imports maps into PocketMine-MP.
* ManyWorlds - a multiple world implementation.
* SignWarp - A sign based teleport facility.

## Available Tools

* rcon - An rcon client.
* pmimporter - Import/Convert into PocketMine-MP.  (Used by ImportMap)

Copyright
=========

    pocketmine-plugins
    Copyright (C) 2013 Alejandro Liu  
    All Rights Reserved.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

Git Recipes
===========

## Keep Dev in sync with master

    git checkout <plugin>-dev
    git merge --no-ff master

## Release

    git checkout <plugin>-dev
    git push ; git pull
    git checkout master
    git push ; git pull
    git merge --no-ff <plugin>-dev
    # ... Check version number ...
    # ... Test phar ...
    git commit -a -m'preparing <plugin> release X.Y'
    git tag -a <plugin>-X.Yrel -m'Release X.Y'
    git push origin --tags
    git push
    git checkout <plugin>-dev
    git merge --no-ff master
    # ... bump version number ...
    git commit -a -m"Bump version number"
    git push origin

## Set-up

    git checkout -b <plugin>-dev master
    git push origin <plugin>-dev
    git push origin --tags


To-do
-----

* Create a Generator based of flat that creates infinite maze
* Port the Minetest Map Generator
* pmimporter: allow for an y-offset to be specify so that
  blocks/tiles/entities can be shifted along side the y axis.
* pmimporter: merge chunks
* pmimporter: Known Entities and Tiles
  * Entities:
      - (Enum)Pos: (Double)x,y,z (0,1,2)
      - (Enum)Motion: (Double)motionX,motionY,motionZ (0,1,2)
      - (Enum)Rotation: (Float) yaw,pitch (0,1)
      - (Float)FallDistance
      - (Short)Fire
      - (Short)Air
      - (Byte)OnGround (1/0)
      - (Byte)Invulerable (1/0)
    Arrow 
      - (Short)Age (Projectile)
    FallingSand
      - (Int)TileID
      - (Byte)Data
    PrimedTNT
      - (Byte)Fuse
    Snowball (Projectile)
      - (Short)Age
    DroppedItem
      - (Compound) Item [
        - (Short) id, Damage
	- (Byte) Count
      - (Short) Health,Age,PickupDelay
      - (String)Owner
      - (String)Thrower
    Villager (Creature|NPC,Ageable)
    Zombie (Monster->Creature)
    Human (Creature|ProjectileSource,InventoryHolder)
    Creature->Living->Damageable
      -(Short)Health
  * Tiles
    Sign
    Chest
    Furnace

(String)id
(Int) x, y, z
SIGN: (String) Text1, Text2, Text3, Text4
CHEST: Items = new Enum("Inventory", []),
	setTagType(NBT::TAG_Compound);
	NBT->Inventory[0..27]
	(Int) pairx, pairz (if double chest)
FURNACE: 	Items (Enum/Inventory , TAG_Compound);
	NBT->Invetory[0..3]
	(Short) BurnTime, CookTime, BurnTicks

* Implement carts?
* ManyWorlds: Enter motd and shows motd (rules) right away
* ManyWorlds: Show if the world will auto load (using `ls`).
* ManyWorlds: Add world to the pocketmine.yml file.
* ManyWorlds: Show rules when entering world event.
