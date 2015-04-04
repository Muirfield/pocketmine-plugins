ItemCasePE
========

* Summary: An implementation of Bukkit's ItemCase
* Dependency Plugins: n/a
* PocketMine-MP version: 1.4 - API 1.10.0
* OptionalPlugins:
* Categories: General
* Plugin Access: Tile Entities, Items/Blocks
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/ItemCasePE)

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

### Permission Nodes:

* itemcase.cmd: allow players access to the itemcase command
* itemcase.destroy: allow players destroy cases

Changes
-------

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

128 70 128
X:-100 Y:69 Z:1072
