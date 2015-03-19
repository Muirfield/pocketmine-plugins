ImportMap
==========

* Summary: Import PC worlds
* Dependency Plugins: n/a
* PocketMine-MP version: 1.4 - API 1.10.0
* OptionalPlugins: ManyWorlds
* Categories: World Editing and Management, Admin Tools
* Plugin Access: Commands, World Editing, Manages Worlds
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/ImportMap)

Overview
--------

A plugin to import PC worlds.

Basic Usage:

* /im *path-to-map* *level*

Documentation
-------------

This plugin imports PC world maps into PocketMine-MP by converting
blocks according to a configurable translation table.

This plugin supports Minecraft PC edition maps in McRegion and Anvil
formats.

The way a world is imported is not very optimized, and may take
a while.

This Plugin calls
[pmimporter](https://github.com/alejandroliu/pocketmine-plugins/tree/master/pmimporter)
to do the heavy lifting.  Since this operation is not something you
would do while playing Minecraft, I recommend using `pmimporter`
directly from the command line instead.  Under Linux, `pmimporter` can
use multiple threads which can speed-up things significantly.


### Command:

im version

Show the version of the `pmimporter` framework.

im *path-to-map* *level*

* path-to-map : Is the file path towards the location of a map.  By
  default the path is based from the PocketMine directory.  You can
  also use a absolute path name.
* level : This is the name that the world be given.

### Configuration

It is recommended that you increase the `async-workers` value to
something other than `1`.  This setting is in `pocketmine.yml`, in hte
`settings` section.

You can configure the translation.  This plugin will create a
`rules.txt` in its data directory.  The format of `rules.txt`
contains:

* comments, start with `;` or `#`.
* `BLOCKS` - indicates the start of a blocks translation rules section.
* `source-block = target-block` is a translation rule.  Any block of
  type `source-block` is converted to `target-block`.

There is a default set of conversion rules, but you can tweak it by
modifying `rules.txt`.

Please look in
[blocks.txt](https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/pmimporter/classlib/pmimporter/blocks.txt)
for block definitions.

Please refer to
[Minecraft PC data values](http://minecraft.gamepedia.com/Data_values)
and
[Minecraft PE data values](http://minecraft.gamepedia.com/Data_values_%28Pocket_Edition%29)
for the values being used.

### Permission Nodes:

* im.cmd.im - Allows users to import maps

### Plugin Issues

* Depending on the map size, conversions can take some time.
* Reported not to work under Windows.

Changes
------

* 2.0 : pmimporter release.
  * Changed to `pmimporter` codebase.
  * Converted to `AsyncTask` implementation.
* 1.0 : First release

Copyright
---------

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
