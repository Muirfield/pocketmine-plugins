pmimporter
==========

* Summary: Import world maps into PocketMine-MP 1.4
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/pmimporter)

Overview
--------

* pmconvert - main conversion tool
* pmcheck - read world maps and analyze the block, object composition
* pmlevel - manipulate some level.dat settings
* nbtdump - Dump the contents of NBT files
* dumpchunk - Extract an specific chunk from a map so it can be
  processed by nbtdump.

Description
-----------

A collection of tools used for importing world maps for use with
PocketMine-MP and Minecraft PE.

It supports the following input formats:

- McRegion (Minecraft PC Edition, PocketMine v1.4)
- Anvil (Minecraft PC Edition)
- PMF (PocketMine v1.3)
- mcpe020 (Minecraft PE 0.2.0-0.8.1)

Currently, it only support McRegion format for output.

When importing Minecraft PC Edition world maps (Anvil and McRegion formats) it
will analyze the used blocks to make sure that only blocks supported
by Minecraft PE are generated.  It does this by either mapping these
blocks or removing them.  This conversion/fitering can be tweaked with
an user provided `rules` file.

Similarly, Tiles and Entities that are not supported by Minecraft PE
are eliminated.

This is done because using these unsupported features on a Minecraft
PE client would cause the game to crash.

Command Usage
-------------

In general, the command usage is:

* _path-to-php-executable_ _path-to-pmimporter.phar_ _sub-command_ [options]

### Sub-commands

#### PMCONVERT

	pmconvert [-c rules.txt ] [-t count] [-f format] srcpath dstpath

Converts maps.

* `-c rules` : Specify a rules conversion file.
* `-t count` : Specifies the number of threads to run.
* `-f format` : Specifies the output format.  Defaults to `mcregion`.
* `-o offset` : Specifies the y-offset for the import.
* `srcpath` : Directory path to the source world.
* `dstpath` : Directory path to create the new world.

Also, `pmconvert` allows you to specify special settings to tweak the
format reader/writer code.  These settings are specifc to each
`format`. To configure you must pass the option:

* `--in.setting=value`  
  for the input format settings.
* `--out.setting-value`  
  for the output format settings.

#### PMCHECK

	pmcheck worldpath [--all|[rX,rZ[:cX,cZ[+cX,cZ]]] ...]

Analyze the number of chunks, blocks, etc in a world map.

* `worldpath` : Directory path to world to analyze.
* `--all` : If specified it will analyze all regions.
* `rX,rZ` : Region X,Z coordinates.
* `:cX,cZ` : Specify individual chunks (in Chunk offsets, from 0 to
  31) to analyze.
* `+cX,cZ` : Additional chunks to add to totals.

#### PMLEVEL

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
* `preset=preset` : Alias for `generatorOptions`.
* `generatorOptions=preset` : Terrain generator `preset` string.
  Ignored by the `normal` generator.  Used by `flat`.

#### NBTDUMP

	nbtdump nbt_file

Dumps the contents of an `NBT` formatted file.

#### DUMPCHUNK

	dumpchunk worldpath rX,rY:cX,cY

Arguments are simmilar to `pmcheck`.

* `worldpath` : Directory path to world to analyze.
* `rX,rZ` : Region X,Z coordinates.
* `:cX,cZ` : Specify individual chunks (in Chunk offsets, from 0 to
  31) to dump.

Settings
--------

Settings are configuration strings that can be used to tweak either
the reading or the writing of maps.  These are format specifc.

## PMF1.3 Settings

- `Xoff` : offsets chunks in the X direction.  Values can be from -15
  to 15, however, unless the values are from 0 (value) to 8, parts of
  the maps will be missing (as they will fall outside the region).
- `Zoff` : offsets chunks in the Z direction.  Values can be from -15
  to 15, however, unless the values are from 0 (value) to 8, parts of
  the maps will be missing (as they will fall outside the region).
- `name` : Changes the reported name of the map.
- `seed` : Changes the reported seed of the map.
- `spawn` : Accepts 3 numbers separated by commas.  For example:
  `128,64,128`.  Changes the reported spawn location.
- `generator` : Changes the reported map generator value.
- `preset` : Changes the reported generator presets value.

## McPe0.2.0 Settings

- `Xoff` : offsets chunks in the X direction.  Values can be from -15
  to 15, however, unless the values are from 0 (value) to 8, parts of
  the maps will be missing (as they will fall outside the region).
- `Zoff` : offsets chunks in the Z direction.  Values can be from -15
  to 15, however, unless the values are from 0 (value) to 8, parts of
  the maps will be missing (as they will fall outside the region).
- `name` : Changes the reported name of the map.
- `seed` : Changes the reported seed of the map.
- `spawn` : Accepts 3 numbers separated by commas.  For example:
  `128,64,128`.  Changes the reported spawn location.
- `generator` : Changes the reported map generator value.
- `preset` : Changes the reported generator presets value.

Installation
------------

Requirements:

* This software has only been tested on Linux
* PHP v5.6.0, version used by PocketMine-MP v1.4.1.  This one contains
  all dependancies.
* PHP CLI API

*pmimporter* comes in three editions:

1. pmimporter.phar - This is the stand-alone version.  If you are only
   using the command line, that is the only version you need.  To
   install copy the `pmimporter.phar` file somewhere in your system
   and run it.  There is no special installation procedure.
2. ImportMap_vX.Y.Z.phar - This is the basic PocketMine-MP plugin.  To
   install copy the `ImportMap_vX.Y.Z.phar` file to your PocketMine-MP
   `plugins` directory.  Look
   [here](https://github.com/alejandroliu/pocketmine-plugins/tree/master/ImportMap)
   for usage instructions.
3. ImportMap-PM.phar - This is a combined command line + plugin
   version in a single file.  To install copy the `ImportMap-PM.phar`
   file to your PocketMine-MP `plugins` directory.  You can then use
   it either from PocketMine or directly from the command-line.

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
* A: You need to upgrade to PocketMine-MP v1.4.1
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

* The only target format implemented is McRegion.
* Anvil maps are silently truncated to be less than 128 blocks high.  
  The PocketMine-MP core API only support Y dimensions for 0 to 127.
* PMF v1.3 maps do not provide valid Entity data so it is ignored.
* Entity data is converted but it is a bit dodgy.  This also has to do
  with the fact that PocketMine itself has incomplete Entity support,
  so I can not properly test those maps.
* Support for Tile and Entity in Anvil and McRegion files is dodgy.

Changes
------

* 1.3: OldSkool fixes
  * Added support for Tiles to PMF maps.
  * Added support for Tiles and Entities fo MCPE 0.2.0 maps.
  * Fixed HeightMap calculations in PMF and MCPE 0.2.0 formats
  * Added `settings` capability to tweak conversion.
* 1.2: Fixes
  * pmcheck: show height map statistics.
  * pmconvert: offset y coordinates
* 1.1: OldSkool release
  * Added support for maps from Minecraft Pocket Edition 0.2.0 - 0.8.1
  * Added support for PMF maps from PocketMine v1.3.
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
