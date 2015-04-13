GrabBag
=======

* Summary: A miscellaneous colletion of commands and listener modules
* Dependency Plugins: n/a
* PocketMine-MP version: 1.4 - API 1.10.0
* DependencyPlugins: -
* OptionalPlugins: -
* Categories: General
* Plugin Access: Commands
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/GrabBag)

Overview
--------

A miscellaneous collection of commands and listener modules.  Features
can be configured to be disable|enable so as to co-exists with other
plugins.


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
* showtimings - Display the info gathered through /timings
* seearmor - show player's armor
* seeinv - show player's inventory
* clearinv - Clear player's inventory
* get - obtain an item
* shield [up|down] - Protect a player
* servicemode [on|off] - enter maintenance mode
* opms [msg] - send op only chat messages
* entities - manage entities
* mute/unmute [player] - mute players
* freeze/thaw [player] - freeze players
* after [secs] cmd - Schedule command
* at [timespec] cmd - Schedule command
* !! - repeat command with changes

Documentation
-------------

This plugin collects a number of commands and listener plugins that I
find useful and wanted to have in a single plugin rather through
multiple ones.  The available commands and listener modules can be
configured.  This allows this plugin to co-exist peacefully with other
plugins.

### Commands:

* *players*  
  Show connected players and locations, health.
* *ops*  
  Display a list of Server Ops and their on-line status.
* *as* *player* *cmd* _[opts]_  
  Run *cmd* as a different *player*.  If you want to send a `chat`
  line as *player* use `chat` for *cmd*.
* *gms*  
  Switch to survival game mode
* *gmc*  
  Switch to creative game mode
* *gma*  
  Switch to adventure game mode
* *slay* *player* _[message]_  
  Kills a player immediatly
* *heal* _[player_ _[value]]_  
  Restore health to a player
* *whois* *player*  
  Show player info
* *showtimings* _[report|clear]_ _[page]_  
  Show the timings data from the `/timings` command.
* *seearmor* *player*  
  Show player's armor
* *seeinv* *player*  
  Show player's inventory
* *clearinv* *player*  
  Clear player's inventory
* *get* *item[:damage]* _[amount]_  
  Obtain an *item*.  When the item name contain spaces in the name,
  use `_` instead.
* *shield* _[up|down]_  
  Show shield status.  Or raise/lower shields.
* *servicemode* _[on|off]_  _[message]_  
  In servicemode, new connections are not allowed.  Existing users are
  OK.  Ops (gb.servicemode.allow) can always login.
* *opms* text  
  Send a message that can only be seen by ops or by the console.  You
  should use the *ops* command to see if there are any server ops
  on-line.
* *mute|unmute* _[player]_  
  Stops players from chatting.  If no player specified it will show a
  list of mutes.
* *freeze|thaw* _[player]_  
  Stops players from moving.  If no player specified it will show a
  list of statues.
* *at* *time* _[:]_ *command*  
  Will schedule to run *command* at the given date/time.  This uses
  php's [strtotime](http://php.net/manual/en/function.strtotime.php)
  function so *times* must follow the format described in
  [Date and Time Formats](http://php.net/manual/en/datetime.formats.php).
* *after* *seconds* *command*  
  Will schedule to run *command* after *seconds*.
* *entities* _level_ _subcommand_  
  By default it will show the current entities.  The following
  sub-commands are available:
  * *tiles*  
    Show tile entities
  * *info* _[e#|t#]_  
    Show details about one or more entities or tiles.
  * *rm* _[e#]_  
    Removes one or more entities.
  * *signN* _[t#]_ _message text_  
    Changes the text line _N_ in the tile/sign identified by _t#_.

Note that commands scheduled with `at` and `after` will only run as
long as the server is running.  These scheduled commands will *not*
survive server reloads or reboots.

### Command repeater

If you want to repeat a previous command enter `!!` *without* any `/`
in front.  This works for commands and chat messages.

You can optionally append additional text to `!!` to do certain
things:

* `!!` number  
  Will let you paginate output.  For example, entering:
	/mw ls
	!!2
	!!3
  This will start showing the output of `/mw ls` and consecutive pages.
* `!!` `/`  
  if you forgot the `/` in front, this command will add it.  Example:
	help
	!!/
* `!!` text  
  Will append `text` to the previous command.  For example:
	/gamemode
	!! survival john
  This will show the usage of survival, the next line will change the
  gamemode of john to survival.
* `!!` str1 str2
  Will repate the previous command replacing `str1` with `str2`
  Example:
	/give player drt
	!!drt dirt
  This will change `drt` into `dirt`.

### Listener Modules

Also this plugin supports the following modules:

* adminjoin : Broadcast a message when an op joins.
* servermotd : Display the server's motd when connecting.
* spawnitems : Initialize a player inventory when they spawn.  
  It will place a configuratble list of inventory items.  Note that it
  only does it for users who start without any inventory.  As soon as
  they start owning stuff, spawnitems will stop working for them.
* spawnarmor : Initialize a player armor when they spawn.  
  I will configure a player's armor through a configurable list.  Note
  that it only does it for users without armor.
* compasstp: When holding a compass tap the screen for 1 second, will
  teleport you in the direciton you are facing.

### Configuration

Configuration is through the `config.yml` file:

	---
	settings:
	  hard-freeze: false
	spawn:
	  armor:
	    head: '-'
	    body: chainmail
	    legs: leather
	    boots: leather
	  items:
	  - "272:0:1"
	  - "17:0:16"
	  - "364:0:5"

	...

Settings:

* `hard-freeze` : if `true` no movement is allowed for frozen
  players.  If `false`, moves are not allowed, but turning is allowed.

The `spawn` section contains two lists:

* `armor`: defines the list of armor that players will spawn with.
* `items`: lists the `item_id`:`damage`:`count` for initial items that
  will be placed in the players inventory at spawn time.

### Activating/De-activating modules

There is a `modules.yml` that by default activates all modules.  You
can de-activate modules by commenting them out from `modules.yml`.
This is done by inserting a `#` in front of the text.


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
* gb.cmd.whois.showip: Allow to view IP addresses
* gb.cmd.seearmor: Show player's armor
* gb.cmd.seeinv: Show player's inventory
* gb.cmd.clearinv: Clear player's inventory
* gb.cmd.get: get blocks.  A shortcut to give.
* gb.spawnarmor.receive: allows player to receive armor when spawning
* gb.spawnitems.receive: allows player to receive items when spawning
* gb.cmd.timings: show timings data
* gb.compasstp.allow : allow player to use a Compass to Teleport
* gb.cmd.shield: Allow players to become invulnerable
* gb.cmd.servicemode: Allow access to service mode command
* gb.servicemode.allow: Allow login when in service mode.
* gb.cmd.entities: Manage entities
* gb.cmd.mute: mute/unmute
* gb.cmd.freeze: freeze/thaw
* gb.cmd.after: Access to command scheduler

Changes
-------
* ???
  * clearinv : new command
  * players : shows game mode
  * et signX : new subcommand
* 1.3.0: More Commands
  * Added !! / to repeater
  * Added freeze and mute commands
  * Added at and after commands
  * Improved entities output
  * Improved documentation
* 1.2.0 : Additional functionality
  * Entities command
  * servermotd module
  * Fixed one warning
* 1.1.2 : Fixes
  * showtimings, added clear operation.
  * "/as": bug fixes
  * Fixed typo in modules.yml
* 1.1.1 : More functionality
  * Hide IP address from whois output
  * New opms command.
  * CompassTP: Prevent teleports to very nearby locations.  Also,
    removed suffocation dangers...  (this is traded with a risk of
    falling from high places...)
  * Added the ability to teleport with a Compass.
  * Added servicemode functionality
  * showtimings command
  * added seearmor, seeinv and get
  * Improved the way how modules.yml is updated
  * added shield command
  * removed un-used old code/re-organized code.
  * Command repeater
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
