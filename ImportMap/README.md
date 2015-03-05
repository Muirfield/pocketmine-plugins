ImportMap
==========

A very naive Plugin to import PC worlds.

Basic Usage:

* /im <path-to-map> <level>

Documentation
-------------

This plugin imports Minecraft PC edition maps in McRegion or Anvil
formats.  In addition it will convert blocks according to a
configurable translation table.

The way a world is imported is not very optimized, and may take
awhile.  Also, while the import is running, the server may be
unavailable.

Use this plugin with a Pocketmine server that is not in use and after
the import is done, copy the resulting files to the actual gaming
Pocketmine server.  If you change the ports being used, you should be
able to run multiple Pocketmine servers on the same computer.

### Command:

`im <path-to-map> <level>`

* path-to-map : Is the file path towards the location of a map.  By
  default the path is based from the PocketMine directory.
* level : This is the name that the world be given.


### Configuration

You can configure the translation.  This plugin will create a
`config.yml` in its data directory.  This file contains pairs:

    source-block-id: target-block-id

Please refer to
[Minecraft PC data values](http://minecraft.gamepedia.com/Data_values)
and
[Minecraft PE data values](http://minecraft.gamepedia.com/Data_values_%28Pocket_Edition%29)
for the values being used.

Issues/Todo
----------

* Entities are unsupported
* Tiles are not supported
* zip files
* Anvil maps are silently truncated to be less than 128 blocks hi.

### Permission Nodes:

* im.cmd.im - Allows users to import maps

Copyright
=========

    ImportMap
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
