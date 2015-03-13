pmimporter
==========

* Summary: Import Minecraft PC worlds into PocketMine-MP
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/pmimporter)

Overview
--------

A tool to import Minecraft PC worlds into PocketMine-MP by converting
unsupported blocks.

* pmconvert - main conversion tool
* pmcheck - read world maps and analyze the block, object composition
* pmlevel - manipulate some level.dat settings
* nbtdump - Dump the contents of NBT files
* plugin - PocketMine plugin version
* dumpchunk - Extract an specific chunk from a map so it can be
  processed by nbtdump.

Description
-----------

A series of scripts used for importing Minecraft PC world maps in
Anvil and McRegion formats into PocketMine.  It does it by converting
unsupported blocks and eliminating unsupported entities.  These
unsupported features when used on a Minecraft PE client would cause
the game to crash.

Command Usage
-------------

	pmconvert [-t count] [-f format] srcpath dstpath

Converts maps.

* `-t count` : Specifies the number of threads to run.
* `-f format` : Specifies the output format.  Defaults to `mcregion`.
* `srcpath` : Directory path to the source world.
* `dstpath` : Directory path to create the new world.

	pmcheck worldpath [--all|[rX,rZ[:cX,cZ[+cX,cZ]]] ...]

Analyze the number of chunks, blocks, etc in a world map.

* `worldpath` : Directory path to world to analyze.
* `--all` : If specified it will analyze all regions.
* `rX,rZ` : Region X,Z coordinates.
* `:cX,cZ` : Specify individual chunks (in Chunk offsets, from 0 to
  31) to analyze.
* `+cX,cZ` : Additional chunks to add to totals.

	pmlevel worldpath [attr=value]

Displays and modifies certain level attributes.

* `worldpath` : Directory path to world to display/modify.
* `attr=value` : Modify the `attr` setting to `value`.

The following attributes are supported:

* `spawn=x,y,z` : World spawn point.
* `name=text` : Level name.
* `seed=integer` : Random Seed.
* `generator=name[,version]` : Terrain generator.  PocketMine by
  default only supports `flat` and `normal`.
* `generatorOptions=preset` : Terrain generator `preset` string.
  Ignored by the `normal` generator.  Used by `flat`.

	nbtdump nbt_file

Dumps the contents of an `NBT` formatted file.

	dumpchunk worldpath rX,rY:cX,cY

Arguments are simmilar to `pmcheck`.

* `worldpath` : Directory path to world to analyze.
* `rX,rZ` : Region X,Z coordinates.
* `:cX,cZ` : Specify individual chunks (in Chunk offsets, from 0 to
  31) to dump.


Installation
------------

The PocketMine-MP plugin can be installed as any PocketMine-MP
plugin. For the stand-alone version, the following applies.

Requirements:

* This software has only been tested on Linux
* PHP v5.6.0
* PHP CLI API



Configuration
-------------

In the directory `classlib/pmimporter` there is a file called
`blocks.txt`.  This file contains block definitions and default
translations.

References
----------

* [Minecraft PC data values](http://minecraft.gamepedia.com/Data_values)
* [Minecraft PE data values](http://minecraft.gamepedia.com/Data_values_%28Pocket_Edition%29)

Issues and Bugs
---------------

* TODO: Anvil output
* Anvil maps are silently truncated to be less than 128 blocks high.  
  The PocketMine-MP core API only support Y dimensions for 0 to 127.
* Entities are copied but they don't show up.
* Tiles are copied but they don't show up.

Changes
------

* 1.0 : First release

Copyright
---------

Some of the code used in this program come from PocketMine-MP,
licensed under GPL.

    pmimporter  
    Copyright (C) 2015 Alejandro Liu  
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
