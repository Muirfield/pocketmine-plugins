GrabBag
=======

* Summary: A miscellaneous colletion of commands
* Dependency Plugins: n/a
* PocketMine-MP version: 1.4 - API 1.10.0
* DependencyPlugins: -
* OptionalPlugins: -
* Categories: General
* Plugin Access: Commands
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/GrabBag)

Overview
--------

A miscellaneous collection of commands

Basic Usage:

* players - show players and their locations
* ops - show ops
* as - run command as somebody else
* gms - Switch to survival mode
* gmc - Switch to creative mode
* gma - Switch to adventure mode
* slay - kill a player
* heal - Restore health to a player
* whois - Info about a player

Documentation
-------------

This plugin collects a number of commands that I find useful and
wanted to have in a single plugin rather through multiple ones.

### Commands:

* *players*  
  Show connected players and locations, health.
* *ops*  
  Display a list of Server Ops and their on-line status.
* *as* *player* *cmd* _[opts]_  
  Run *cmd* as a different *player*.
* *gms*  
  Switch to survival game mode
* *gmc*  
  Switch to creative game mode
* *slay* *player* _[message]_  
  Kills a player immediatly
* *heal* _[player_ _[value]]_  
  Restore health to a player
* *whois* *player*  
  Show player info

### Listener Modules

Also this plugin supports the following modules:

* adminjoin : Broadcast a message when an op joins.
* spawnitems : Initialize a player inventory when they spawn.  
  It will place a configuratble list of inventory items.  Note that it
  only does it for users who start without any inventory.  As soon as
  they start owning stuff, spawnitems will stop working for them.
* spawnarmor : Initialize a player armor when they spawn.  
  I will configure a player's armor through a configurable list.  Note
  that it only does it for users without armor.

### Configuration

You can configure the `spawnitems` and `spawnarmor` modules from `config.yml`.

### Activating/De-activating modules

There is a `modules.yml` that by default activates all modules.  You
can de-activate modules by commenting them out from `modules.yml`.

### Permission Nodes:

* gb.cmd.players - Allow players command
* gb.cmd.ops - list server ops
* gb.cmd.sudo - allow to run commands as somebody else
* gb.cmd.gms: allow switch gamemode to survival
* gb.cmd.gmc: allow switch gamemode to creative
* gb.cmd.gma: allow switch gamemode to adventure
* gb.cmd.slay: kill other players
* gb.cmd.heal: healing
* gb.cmd.whois: show player info
* gb.spawnarmor.receive: allows player to receive armor when spawning
* gb.spawnitems.receive: allows player to receive items when spawning

Changes
-------

* 1.0.0 : First public release

Copyright
---------

    GrabBag  
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
