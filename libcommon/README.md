<img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/common.png" style="width:64px;height:64px" width="64" height="64"/>

# libcommon

- Summary: aliuly's common library
- Dependency Plugins: n/a
- PocketMine-MP version: 1.4 (API:1.10.0)
- DependencyPlugins: -
- OptionalPlugins: -
- Categories: DevTools
- Plugin Access: -
- WebSite: https://github.com/alejandroliu/pocketmine-plugins/tree/master/libcommon

## Overview

This plugin contains my standard library that I personally use when
writing PocketMine-MP plugins.  Its main value as a stand-alone **phar**
is when writing script plugins.  As it provide useful functionality that
can be called directly by these scripts.

For the most up to date documentation visit
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/libcommon).

This plugin can be downloaded from its
[Downloads](https://github.com/alejandroliu/pocketmine-plugins/tree/master/libcommon/downloads.md)
<img src="https://raw.githubusercontent.com/alejandroliu/bad-plugins/master/Media/download-icon.png" alt="Downloads"/>
page.

Contains quite a few utility functions. Available modules:

- ArmorItems - Utilities to handle Armor/Item relations
- BasicCli - My class to impelements commands and/or sub-commands
- BasicHelp - Implements help functionality for sub-commands
- BasicPlugin - My extensions to PluginBase
- Cmd - execute PocketMine commands from a PHP module.
- CmdSelector - command selector implementation
- ExpandVars - utility for variable expansions
- FileUtils - Generic file system functions
- FreezeSession - Session implementing frozen players
- GetMotd - Retrieve motd data from remote servers
- GetMotdAsyncTask - An AsyncTask wrapper for GetMotd
- InvisibleSession - Invisible players
- ItemName - Return object names
- mc - translation
- MoneyAPI - supports the following economy plugins:
  - GoldStd
  - PocketMoney
  - EconomyAPI
  - MassiveEconomy
- MPMU - miscellaneous utilities
- Npc - Non-player character functionality
- PluginAsyncTask - A simple wrapper around AsyncTask
- PluginCallbackTask - Replacement for the CallbackTask deprecated functionality
- PMScript - scripting routines
- QueryAsyncTask - An AsyncTask wrapper for MinecraftQuery
- Rcon - Rcon functionality
- RconTask - An AsyncTask wrapper for Rcon
- Session - Basic session management
- SubCommandMap - Enssemble sub command implementation

Features:

- Paginated output
- Command/Sub-command registration
- Player state management
- Config shortcuts and multi-module|feature management
- Translations
- Multiple economy supports
- API version checking
- Plugin shortcuts, etc...

It also bundles useful third party libraries:

- xPaw MinecraftQuery

For the full API documentation go to: [GitHub pages](http://alejandroliu.github.io/pocketmine-plugins/libcommon/apidocs/index.html)

## Commands

Also, for debugging purposes, the **libcommon** command is provided, which
has the following sub-commands:

* **libcommon** **version** : Show the libcommon version.

These sub-commands are only available if **debug** level > 1
in **pocketmine.yml**:

* **libcommon** **dumpmsg** _plugin_ : Dump `messages.ini` from the given
  plugin.
* **libcommon** **echo** _text_ : Display the given text (does variable
  substitution)

## Examples

If you enable **debug** level > 1 in **pocketmine.yml** the plugin will create
a **libcommon** folder with some example scripts on how to use this library.

Other wise you can follow this
<a href="https://github.com/alejandroliu/pocketmine-plugins/tree/master/libcommon/resources/examples" target="_new" title="_examples_" >
Link.
</a>

## Changes

- 1.2.0: Update 2<br/>
  * MoneyAPI bug fix<br/>
  * Fixed BasicPlugin bug<br/>
  * New Features:<br/>
    * GetMotdAsyncTask<br/>
    * Session management<br/>
    * FileUtils<br/>
    * ArmorItems<br/>
    * Variables<br/>
    * ItemName can load user defined tables<br/>
    * SubCommandMap spinned-off from BasicPlugin<br/>
- 1.1.0: Update 1<br/>
  * Added ItemName class (with more item names)<br/>
  * Removed MPMU::itemName<br/>
- 1.0.0: First release

## Copyright

libcommon<br/>
Copyright (C) 2015 Alejandro Liu<br/>
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

