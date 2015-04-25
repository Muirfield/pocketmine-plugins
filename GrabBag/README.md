# GrabBag

**DOCUMENTATION IS A MESS**


* Summary: A miscellaneous colletion of commands and listener modules
* Dependency Plugins: n/a
* PocketMine-MP version: 1.4 - API 1.10.0
* DependencyPlugins: -
* OptionalPlugins: -
* Categories: General
* Plugin Access: Commands
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/GrabBag)

## Overview


A miscellaneous collection of commands and listener modules.  Features
can be configured to be disable|enable so as to co-exists with other
plugins.

This plugin is focused more towards commands to help system
administration.

### Informational

* players - show players and their locations
* ops - show ops
* whois - Info about a player
* showtimings - Display the info gathered through /timings
  * showtimings - shows available reports.
  * showtimings report <rpt> - show the the report <rpt>
  * showtimings clear - deletes timing reports

### Player management

* gms - Switch to survival mode
* gmc - Switch to creative mode
* gma - Switch to adventure mode
* as - run command as somebody else
  * as <player> <cmd>
* shield [up|down] - Protect a player

### Inventory Management

* get - obtain an item
  * get <item>
* seearmor <player> - show player's armor *broken in 1.5?*
* setarmor - armor up (works also in creative mode)
  * setarmor [player] [head|body|legs|boots] >leather|chainmail|iron|gold|diamond>
* seeinv [player] - show player's inventory
* clearinv [player] - Clear player's inventory

### Trolling

* slay <player> [message] - kills a player
* heal [player] [ammount] - Restore health to a player
* mute/unmute [player] - mute players
* freeze/thaw [player] - freeze players
* burn <player> [secs] - Burn player for a number of secs
* throw <player> [force] - throw player up in the air, with the
  specified force numeric value.
* blowup <player> [yield|magic|normal] - explode player with the given
  yield (a number), with magic (no damage to blocks) or normal.
* spectator/unspectator <player> - turns a player into an spectator.
  Spectator can move in a world but can not interact with it.  Can not
  open chests, place/break blocks, fight etc.

### Server Management

* servicemode [on|off] [message] - enter maintenance mode.  When in
  maintenance mode, only ops can join the server.
* opms [msg] - send op only chat messages
* after [secs] cmd - Schedule command
* at [timespec] cmd - Schedule command
* rpt [message] - report issues to ops
* rcon - an rcon client

### Entity Management

* entities - manage entities

### Teleporting

* spawn - teleport to spawn point
* summon <player> [msg] - teleports <player> to you.
* dismiss <player> [msg] - teleports <player> back to where they were
  summoned.
* pushtp <x y z|player|world> - save current location and teleport
* poptp - goes back to saved location
* followers - list who is following who
* follow - follow a player
* followme - make people follow you
* follow-off - stop following a player
* followme-off - make peole stop following you

### Command Entry

* prefix <prefix text> - Prepend prefix to commands (to run multiple
  `/as player` commands in a row).
* !! - repeat command with changes

### Misc Modules

* joins - Show MOTD and when an admin joins.

## Documentation

**NOTE: In v2.0.0 Configuration has been changed**

This plugin collects a number of commands and listener plugins that I
find useful and wanted to have in a single plugin rather through
multiple ones.  The available commands and listener modules can be
configured.  This allows this plugin to co-exist peacefully with other
plugins.

### Command Reference

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
* *entities* _subcommand_ _[options]  
  By default it will show the current entities.  The following
  sub-commands are available:
  * *ls* _[world]_  
    Show entities in _[world]_ (or current world if not specified).
  * *tiles* _[world]_  
    Show tile entities in _[world]_ (or current world if not specified).
  * *info* _[e#|t#]_  
    Show details about one or more entities or tiles.
  * *rm* _[e#]_  
    Removes one or more entities.
  * *signN* _[t#]_ _message text_  
    Changes the text line _N_ in the tile/sign identified by _t#_.
  * *count*  
    Show a count of the number of entities on the server.
  * *nuke* _[all|mobs|others]_  
    Clear entities from the server.
* *rpt* _[text]_
  Report issues to server ops.  Sub commands:
  * read
    Show messages
  * clear _[m#|all]_
    Clears messages
* *summon* <player> [msg]  
  teleports <player> to you.
* *dismiss* <player> [msg]  
  teleports <player> back to where they were summoned from.
* *pushtp* <x y z|player|world>
  save current location and teleport (if desired).
* *poptp*
  goes back to saved location.  Push and pop work in a stack.  Push
  will save the current location on the top of the stack, while pop
  will pop it from the top of the stack.
* *prefix* _[-n]_ <text>  
  Will prepend `<text>` to the following commands.  Enter */prefix* on
  its own to stop.  This is useful when entering multipe `/as
  <player>`  commands in a row.  The _-n_ options will *not* insert a
  space between `prefix` and `command`.
* *spawn*  
  Teleport to spawn point.
* burn <player> [secs]  
  Burn player for the specified number of seconds
* throw <player> [force]  
  throw player up in the air.  The force tells how high to go.
* blowup <player> [yield|magic|normal]  
  explode player.  Yield is a number that specifies the force of the explosion.

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
  Will repeat the previous command replacing `str1` with `str2`
  Example:
	/give player drt
	!!drt dirt
  This will change `drt` into `dirt`.
* `!!`^ text  
  Will insert `text` at the beginning of the command.

### Listener Modules

Also this plugin supports the following modules:

* adminjoin : Broadcast a message when an op joins.
* servermotd : Display the server's motd when connecting.

### Configuration

Configuration is through the `config.yml` file:

	commands:
	  players: true
	  ops: true
	  gm?: true
	  as: true
	  slay: true
	  heal: true
	  whois: true
	  showtimings: true
	  seeinv-seearmor: true
	  clearinv: true
	  get: true
	  shield: true
	  servicemode: true
	  opms: true
	  entitites: true
	  mute-unmute: true
	  freeze-thaw: true
	  after-at: true
	  rpt: true
	  summon-dismiss: true
	  pushtp-poptp: true
	  prefix: true
	  opms-rpt: true
	  srvmode: true
	  entities: true
	  spawn: true
	modules:
	  adminjoin: true
	  servermotd: true

	  repeater: true
	freeze-thaw:
	  hard-freeze: false

* commands: This is a list of available commands.  Set to true to
  activate commands, false ot deactivate.
* modules: List of available modules.  Set true to activate, false to
  deactivate.
* freeze-thaw
  * `hard-freeze` : if `true` no movement is allowed for frozen
     players.  If `false`, moves are not allowed, but turning is
     allowed.

### Permission Nodes:

* gb.module.repeater: Access to repeater module
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
* gb.cmd.timings: show timings data
* gb.cmd.shield: Allow players to become invulnerable
* gb.cmd.servicemode: Allow access to service mode command
* gb.servicemode.allow: Allow login when in service mode.
* gb.cmd.entities: Manage entities
* gb.cmd.mute: mute/unmute
* gb.cmd.freeze: freeze/thaw
* gb.cmd.after: Access to command scheduler
* gb.cmd.rpt: Report issues
* gb.cmd.rpt.read: Read reported issues
* gb.cmd.summon: Access to summon/dismiss command
* gb.cmd.pushpoptp: Access to push/pop teleport
* gb.cmd.prefix: Access to /prefix
* gb.cmd.spawn: Access to /spawn

# Changes

* 2.0.0: Re-factoring
  * Re-factoring the code so it is more maintainable
  * Removed unbreakable (moved to WorldProtect), CompassTP (moved to
    ToyBox), spawnitems, spawnarmor (moved to SpawnControl).
  * Added /prefix, /spawn
  * /et subcommands: count and nuke, overall simplified entities.
* 1.4.1: maintenance
  * Fixed a bug in showtimings.
  * Fixed improper usage of the API in Removing Tile and Entities.
  * Changed so unbreakable is off by default
* 1.4.0
  * new commands: clearinv, rpt
  * new listeners: unbreakable
  * players : shows game mode
  * et signX : new subcommand
  * summon/dismiss commands
  * push/pop teleport commands
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

# Copyright

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

* * *
