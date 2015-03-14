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
* dumpchunk - Extract an specific chunk from a map so it can be
  processed by nbtdump.

Description
-----------

A collection of tools used for importing Minecraft PC world maps in
Anvil and McRegion formats into PocketMine.  It does it by converting
unsupported blocks and eliminating unsupported entities.  These
unsupported features when used on a Minecraft PE client would cause
the game to crash.

Command Usage
-------------

	pmconvert [-c rules.txt ] [-t count] [-f format] srcpath dstpath

Converts maps.

* `-c rules` : Specify a rules conversion file.
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

Download the `pmimporter.phar` file, and run.

Configuration
-------------

You can configure the translation by providing a `rules` file and
passing it to `pmcovert` with the `-c` option. The format of `rules.txt`
is as follows:

* comments, start with `;` or `#`.
* `BLOCKS` - indicates the start of a blocks translation rules section.
* `source-block = target-block` is a translation rule.  Any block of
  type `source-block` is converted to `target-block`.

There is a default set of conversion rules, but you can tweak it by
using `rules`.

FAQ
---

* Q: Why tall builds seem to be chopped off at te top?
* A: That is a limitation of Pocket Edition.  It only supports chunks
  that are up to 128 blocks high, while the PC edition Anvil worlds
  can support more.
* Q: Why my Anvil format file is not recognized?
* A: That happens with Anvil files that were converted from an
  McRegion file.  These files contain both `.mcr` and .`mca` files.
  These confuses the File format recognizer.  You need to delete the
  `.mcr` files so the world is recognized as in Anvil format.
* Q: Why I experience glitches when I enter a new world?
* A: This is a Minecraft Pocket Edition limitation.  This is made
  worse by spawning into a very large chunk (usually very detailed
  builds). My recommendation is to change the spawn point to a very
  flat (boring) area.  Sometimes exting and re-entering the game
  helps.
* Q: Why I get corrupted chunks after I modify some (very detailed) areas?
* A: The current release of PocketMine-MP (1.4) has a bug where large
  chunks get overlapped causing chunk corruption.
* Q: Why I see some blocks that are not in the original map?
* A: These have to do with how the translation happens.  There are
  blocks that are not supported by Minecraft Pocket Edition.  These
  need to be map to a block supported by MCPE.  You can tweak this by
  modifying the conversion rules.
* Q: Why do converted maps overload my server?
* A: Detailed maps need to be uncompressed by the server.  These take
  an additional load on the server.

References
----------

* [Block defintions](https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/pmimporter/classlib/pmimporter/blocks.txt)
* [Minecraft PC data values](http://minecraft.gamepedia.com/Data_values)
* [Minecraft PE data values](http://minecraft.gamepedia.com/Data_values_%28Pocket_Edition%29)

Issues and Bugs
---------------

* TODO: Anvil output.
* Anvil maps are silently truncated to be less than 128 blocks high.  
  The PocketMine-MP core API only support Y dimensions for 0 to 127.


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
