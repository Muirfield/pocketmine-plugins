<img src="https://raw.githubusercontent.com/Muirfield/pocketmine-plugins/master/Media/ItemCase-icon.png" style="width:64px;height:64px" width="64" height="64"/>

ItemCasePE
=========

* Summary: An implementation of Bukkit's ItemCase
* Dependency Plugins: n/a
* PocketMine-MP version: 1.4 - API 1.10.0, 1.6 - API 1.13.0
* OptionalPlugins:
* Categories: General
* Plugin Access: Tile Entities, Items/Blocks
* WebSite: [github](https://github.com/Muirfield/pocketmine-plugins/tree/master/ItemCasePE)

Overview
--------

A simplistic implementation of Bukkit's ItemCase.

Command:

* /itemcase add

Then while holding an item, tap on a slab or glass block.  This will create an
itemcase with that item.

To destroy, just destroy the block where the itemcase was placed.


Documentation
-------------

Case data is placed on a file in the world folder.  This is
loaded during world load and unloaded during unload.

Available sub-commands:

* /itemcase add  
  Adds an ItemCase with object on hand.
* /itemcase cancel  
  Aborts an `add` operation.
* /itemcase respawn  
  Debug sub-command.  Re-creates all ItemCases.

### Configuring

By default you need to place items on Slabs or on Glass blocks.  This
is **classic** mode.  You can, if you want, enable **new wave** mode.
This would let you place items on any type of blocks.  To enable this,
modify the `config.yml` file:

    settings:
      classic: true

Change the line `classic` from `true` to `false`.  Note that **new
wave** mode is experimental and has not been fully tested.

### Permission Nodes:

* itemcase.cmd: allow players access to the itemcase command
* itemcase.destroy: allow players destroy cases

## Changes

* 1.1.0 : Updated for API 2.0.0
  - Fixes #63.
* 1.0.8 : No AIR cases
  - Do not allow to place cases with AIR only.  Reported by @Pub4Game.
    Closes #30.
* 1.0.7 : Update for PM1.6dev
  - Checks which function to call (isPlaceable/canBePlaced) without having
    to check version.
* 1.0.6 : Update for PM1.6dev
  - changed isPlaceable for canBePlaced
* 1.0.5 : BugFix
  - Fixed a small bug related to new wave mode.
* 1.0.4 : new wave vs classic
  - Added new wave mode that allows you to place itemcases everywhere.
  - Removed callbacktask deprecation warning
* 1.0.2 : Bugfix
  - Fixed bugs and improved permissions.
* 1.0.1 : Bugfix
  - Fixed despawn when chunk unloads
* 1.0.0 : First release

Copyright
=========

    ItemCasePE
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
