# GrabBag

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

### Entity Management

* entities : entity management

### Informational

* ops : Shows who are the ops on this server.
* players : Shows what players are on-line
* showtimings : Shows timing repots as reported by `/timings`
* whois : Gives detail information on players

### Inventory Management

* clearinv : Clear player's inventory
* get : obtain an item
* seearmor : Show player's armor
* seeinv : Show player's inventory
* setarmor : Sets armor (even in creative)

### Player Management

* as : run command as somebody else
* gma : Change your gamemode to _Adventure_.
* gmc : Change your gamemode to _Creative_.
* gms : Change your gamemode to _Survival_.
* prefix : prepend prefix to chat lines
* shield : player is protected from taking damage

### Server Management

* after : schedule command after a number of seconds
* at : schedule command at an appointed date/time
* opms : sends a message to ops only
* rcon : rcon client
* rpt : report an issue to ops
* servicemode : controls servicemode

### Teleporting

* dismiss : Dismiss a previously summoned player
* follow : Follow a player
* follow-off : stop following a player
* followers : List who is following who
* followme : Make a player follow you
* followme-off : stop making a player follow you
* poptp : Returns to the previous location
* pushtp : Saves current location and teleport
* spawn : Teleport player to spawn point
* summon : Summons a player to your location

### Trolling

* blowup : explode a player
* burn : Burns the specified player
* freeze|thaw : freeze/unfreeze a player so they cannot move.
* heal : Restore health to a player
* mute|unmute : mutes/unmutes a player so they can not use chat
* slay : Kills the specified player
* spectator|unspectator : toggle a player's spectator mode
* throw : Throw a player in the air

### Modules

* join-mgr : Announce joining ops, and show server motd
* repeater : Uses `!!` to repeat command with changes

## Documentation

**NOTE: In v2.0.0 Configuration has been changed**

This plugin collects a number of commands and listener plugins that I
find useful and wanted to have in a single plugin rather through
multiple ones.  The available commands and listener modules can be
configured.  This allows this plugin to co-exist peacefully with other
plugins.

### Command Reference

The following commands are available:

* **after** _<seconds>_ _<command>_  
  schedule command after a number of seconds  

  Will schedule to run *command* after *seconds*

* **as** _<player>_ _<command>_  
  run command as somebody else  

* **at** _<time>_ _[:]_ _command_  
  schedule command at an appointed date/time  

  Will schedule to run *command* at the given date/time.  This uses
  php's [strtotime](http://php.net/manual/en/function.strtotime.php)
  function so _times_ must follow the format described in
  [Date and Time Formats](http://php.net/manual/en/datetime.formats.php).

* **blowup** _<player>_ _[yield]_ **[magic]** **[normal]**  
  explode a player  

  Explodes `player` with an explosion with the given `yield` (a number).
  If `magic` is specified no damage will be taken by blocks.  The
  default is `normal`, where blocks do get damaged.

* **burn** _<player>_ _[secs]_  
  Burns the specified player  

  Sets `player` on fire for the specified number of seconds.
  Default is 15 seconds.

* **clearinv** _<player>_  
  Clear player's inventory  

* **dismiss** _<player>_ _[message]_  
  Dismiss a previously summoned player  

* **entities** _[subcommand_ _[options]_  
  entity management  

  By default it will show the current entities.  The following
  sub-commands are available:
  - **entities** **ls** _[world]_
     - Show entities in _[world]_ (or current world if not specified).
  - **entities** **tiles** _[world]_
     - Show tile entities in _[world]_ (or current world if not specified).
  - **entities** **info** _[e#|t#]_
    - Show details about one or more entities or tiles.
  - **entities** **rm** _[e#]_
    - Removes one or more entities.
  - **entities** **sign**_N_ _[t#]_ _message text_
    - Changes the text line _N_ in the tile/sign identified by _t#_.
  - **entities** **count**
    - Show a count of the number of entities on the server.
  - **entities** **nuke** _[all|mobs|others]_
    -Clear entities from the server.

* **follow** _<player>_  
  Follow a player  
* **follow-off**  
  stop following a player  
* **followers**  
  List who is following who  
* **folowme** _<player>_  
  Make a player follow you  
* **followme-off** _<player>_  
  stop making a player follow you  

* **freeze|thaw** [_player_|**--hard|--soft**]  
  freeze/unfreeze a player so they cannot move.  

  Stops players from moving.  If no player specified it will show
  the list of frozen players.

  If `--hard` or `--soft` is specified instead of a player name, it
  will change the freeze mode.

* **get** _<item>_  
  obtain an item  

  This is a shortcut to `/give` that lets player get items for
  themselves.

* **gma**  
  Change your gamemode to _Adventure_.  

* **gmc**  
  Change your gamemode to _Creative_.  
* **gms**  
  Change your gamemode to _Survival_.  
* **heal** _[player]_ _[ammount]_  
  Restore health to a player  

  Heals a player.  If the amount is positive it will heal, if negative
  the player will be hurt.  The units are in 1/2 hearts.

* **mute|unmute** _[player]_  
  mutes/unmutes a player so they can not use chat  

  Stops players from chatting.  If no player specified it will show
  the list of muted players.

* **opms** _[msg]_  
  sends a message to ops only  

  Sends chat messages that are only see by ops.  Only works with ops
  that are on-line at the moment.  If you no ops are on-line you
  should use the `rpt` command.

* **ops**  
  Shows who are the ops on this server.  
* **players**  
  Shows what players are on-line  
* **poptp**  
  Returns to the previous location  

* **prefix** _[-n]_ _<prefix text>_  
  prepend prefix to chat lines  

  This allows you to prepend a prefix to chat lines.
  To stop enter `/prefix` by itself (or `prefix` at the console).
  Usage examples:

  - Send multiple `/as player` commands in a row.
  - Start a private chat `/tell player` with another player.
  - You prefer commands over chat: `-n /`

* **pushtp** _<player>_ _[target]_  
  Saves current location and teleport  
* **rcon** **[--add|--rm|--ls|id]** _<command>_  
  rcon client  

  This is an rcon client that you can used to send commands to other
  remote servers.  Options:
  - **rcon --add** _<id>_ _<address>_ _<port>_ _<password>_ _[comments]_
    - adds a `rcon` connection with `id`.
  - **rcon --rm** _<id>_
    - Removes `rcon` connection `id`.
  - **rcon --ls**
    - List configured rcon connections.
  - **rcon** _<id>_ _<command>_
    - Sends the `command` to the connection `id`.
  should use the `rpt` command.

* **rpt** [_message_|**read|clear** _<all|##>_]  
  report an issue to ops  

  Logs/reports an issue to server ops.  These issues are stored in a
  a file which can be later read by the server operators.  Use this
  when there are **no** ops on-line.  If there are ops on-line you
  should use the `opms` command.

  The following ops only commands are available:
  - **rpt** **read** _[##]_
    - reads reports.  You can specify the page by specifying a number.
  - **rpt** **clear** _<all|##>_
    - will delete the specified report or if `all`, all the reports.

* **seearmor** _<player>_  
  Show player's armor  

* **seeinv** _<player>_  
  Show player's inventory  
* **servicemode** **[on|off** _[message]_ **]**  
  controls servicemode  

  If `on` it will activate service mode.  In service mode new
  players can not join (unless they are ops).  Existing players
  can remain but may be kicked manually by any ops.

* **setarmor** _[player]_ _[piece]_ _<type>_  
  Sets armor (even in creative)  

  This command lets you armor up.  It can armor up creative players too.
  If no `player` is given, the player giving the command will be armored.

  Piece can be one of `head`, `body`, `legs`, or `boots`.

  Type can be one of `leather`, `chainmail`, `iron`, `gold` or `diamond`.

* **shield**  
  player is protected from taking damage  

  This will toggle your shield status.

* **timings** _[t#]_  
  Shows timing repots as reported by `/timings`  

  If nothing specified it will list available reports.  These are
  of the form of `timings.txt` or `timings1.txt`.

  To specify a report enter `t` for `timings.txt` or `t1` for
  `timings1.txt`.
* **slay** _<player>_ _[msg]_  
  Kills the specified player  

  Kills a player with an optional `message`.

* **spawn**  
  Teleport player to spawn point  

* **spectator|unspectator** _[player]_  
  toggle a player's spectator mode  

  `/spectator` will turn a player into an spectator.  In this mode
  players can move but not interact (i.e. can't take/give damage,
  can't place/break blocks, etc).

  If no player was specified, it will list spectators.
* **summon** _<player>_ _[message]_  
  Summons a player to your location  
* **throw** _<player>_ _[force]_  
  Throw a player in the air  
* **whois** _<player>_  
  Gives detail information on players  


Commands scheduled by `at` and `after` will only run as
long as the server is running.  These scheduled commands will *not*
survive server reloads or reboots.


### Module reference

#### join-mgr

Announce joining ops, and show server motd

This listener module will broadcast a message for ops joining
a server.

Also, it will show the server's motd on connect.

#### repeater

Uses `!!` to repeat command with changes

If you want to repeat a previous command enter `!!` *without* any `/`
in front.  This works for commands and chat messages.

You can optionally append additional text to `!!` to do certain
things:

* `!!` number
  - Will let you paginate output.  For example, entering:
		/mw ls
		!!2
		!!3
  This will start showing the output of `/mw ls` and consecutive pages.
* `!!` `/`
  - if you forgot the `/` in front, this command will add it.  Example:
		help
		!!/
* `!!` text
  - Will append `text` to the previous command.  For example:
		/gamemode
		!! survival john
  This will show the usage of survival, the next line will change the
  gamemode of john to survival.
* `!!` str1 str2
  - Will repeat the previous command replacing `str1` with `str2`
    Example:
		/give player drt
		!!drt dirt
  This will change `drt` into `dirt`.
* `!!`^ text
  - Will insert `text` at the beginning of the command.


### Configuration

Configuration is throug the `config.yml` file.
The following sections are defined:

#### features


This section you can enable/disable commands and listener modules.
You do this in order to avoid conflicts between different
PocketMine-MP plugins.  It has one line per feature:

   feature: true|false

If `true` the feature is enabled.  if `false` the feature is disabled.

#### freeze-thaw


* hard-freeze (false): if `true` no movement is allowed.  If `false`,
  turning is allowed but not walking/running/flying, etc.

#### join-mgr


* adminjoin - broadcast whenever an op joins
* servermotd - show the server's motd when joining

#### rcon-client


This section configures the rcon client connections.  You can configure
this section through the *rcon* command.


# Changes

* 2.0.0: Re-factoring
  * Re-factoring the code so it is more maintainable
  * Removed unbreakable (moved to WorldProtect), CompassTP (moved to
    ToyBox), spawnitems, spawnarmor (moved to SpawnMgr).
  * Added /prefix, /spawn, etc...
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

