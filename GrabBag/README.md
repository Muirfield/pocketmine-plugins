<img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/GrabBag-icon.png" style="width:64px;height:64px" width="64" height="64"/>

# GrabBag

* Summary: Collection of miscellaneous commands and listener modules
* Dependency Plugins: n/a
* PocketMine-MP version: 1.4 (API:1.10.0), 1.4.1 (API:1.11.0), 1.5 (API:1.12.0)
* DependencyPlugins: -
* OptionalPlugins: -
* Categories: General
* Plugin Access: Commands, Data Saving, Entities, Tile Entities,
  Manages plugins
* WebSite: https://github.com/alejandroliu/pocketmine-plugins/tree/master/GrabBag

## Overview

<!-- php: $v_forum_thread = "http://forums.pocketmine.net/threads/grabbag.7524/"; -->
<!-- template: prologue.md -->

**DO NOT POST QUESTION/BUG-REPORTS/REQUESTS IN THE REVIEWS**

It is difficult to carry a conversation in the reviews.  If you
have a question/bug-report/request please use the
[Thread](http://forums.pocketmine.net/threads/grabbag.7524/) for
that.  You are more likely to get a response and help that way.

**NOTE:**

This documentation was last updated for version **2.2.2dev**.

Please go to
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/GrabBag)
for the most up-to-date documentation.

You can also download this plugin from this [page](https://github.com/alejandroliu/pocketmine-plugins/releases/tag//GrabBag-2.2.2dev).

<!-- template-end -->

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

* clearhotbar : Clear player's hotbar
* clearinv : Clear player's inventory
* get : obtain an item
* seearmor : Show player's armor
* seeinv : Show player's inventory
* setarmor : Sets armor (even in creative)

### Player Management

* as : run command as somebody else
* fly : Toggle flying **ONLY FOR PM1.5**
* gma : Change your gamemode to _Adventure_.
* gmc : Change your gamemode to _Creative_.
* gms : Change your gamemode to _Survival_.
* gmspc : Change your gamemode to _Spectator_.
* perm : temporarily change player's permissions
* prefix : prepend prefix to chat lines
* reg : Manage player registrations
* shield : player is protected from taking damage
* skin : manage player's skins

### Server Management

* after : schedule command after a number of seconds
* at : schedule command at an appointed date/time
* crash : manage crash dumps
* opms : sends a message to ops only
* pluginmgr : manage plugins
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

* blood-particles : Display particles when a player gets hit
* broadcast-ft : Broadcast player's using FastTransfer
* broadcast-tp : Broadcast player's teleports
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

* **after** _&lt;seconds&gt;_ _&lt;command&gt;_|list|cancel _&lt;id&gt;_  
  schedule command after a number of seconds  

  Will schedule to run *command* after *seconds*.
  The **list** sub command will show all the queued commands.
  The **cancel** sub command allows you to cancel queued commands.

* **as** _&lt;player&gt;_ _&lt;command&gt;_  
  run command as somebody else  

* **at** _&lt;time&gt;_ _[:]_ _command_|list|cancel _&lt;id&gt;_  
  schedule command at an appointed date/time  

  Will schedule to run *command* at the given date/time.  This uses
  php's [strtotime](http://php.net/manual/en/function.strtotime.php)
  function so _times_ must follow the format described in
  [Date and Time Formats](http://php.net/manual/en/datetime.formats.php).
  The **list** sub command will show all the queued commands.
  The **cancel** sub command allows you to cancel queued commands.

* **blowup** _&lt;player&gt;_ _[yield]_ **[magic]** **[normal]**  
  explode a player  

  Explodes `player` with an explosion with the given `yield` (a number).
  If `magic` is specified no damage will be taken by blocks.  The
  default is `normal`, where blocks do get damaged.

* **burn** _&lt;player&gt;_ _[secs]_  
  Burns the specified player  

  Sets `player` on fire for the specified number of seconds.
  Default is 15 seconds.

* **clearhotbar** _[player]_  
  Clear player's hotbar  

* **clearinv** _[player]_  
  Clear player's inventory  
* **crash** _[ls|clean|show]_  
  manage crash dumps  

  Will show the number of `crash` files in the server.
  The following optional sub-commands are available:
  - **crash** **count**
    - Count the number of crash files
  - **crash** **ls** _[patthern]_
    - List crash files
  - **crash** **clean** _[pattern]_
    - Delete crash files
  - **show** _[pattern]_
    - Shows the crash file ##

* **dismiss** _&lt;player&gt;_ _[message]_  
  Dismiss a previously summoned player  

* **entities** _[subcommand]_ _[options]_  
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

* **fly**  
  Toggle flying **ONLY FOR PM1.5**  

* **follow** _&lt;player&gt;_  
  Follow a player  
* **follow-off**  
  stop following a player  
* **followers**  
  List who is following who  
* **folowme** _&lt;player&gt;_  
  Make a player follow you  
* **followme-off** _&lt;player&gt;_  
  stop making a player follow you  

* **freeze|thaw** [_player_|**--hard|--soft**]  
  freeze/unfreeze a player so they cannot move.  

  Stops players from moving.  If no player specified it will show
  the list of frozen players.

  If `--hard` or `--soft` is specified instead of a player name, it
  will change the freeze mode.

* **get** _&lt;item&gt;_  
  obtain an item  

  This is a shortcut to `/give` that lets player get items for
  themselves.

* **gma**  
  Change your gamemode to _Adventure_.  
* **gmc**  
  Change your gamemode to _Creative_.  
* **gms**  
  Change your gamemode to _Survival_.  
* **gmspc**  
  Change your gamemode to _Spectator_.  

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
* **perm** _&lt;player&gt;_ _&lt;dump|permission&gt; _[true|false]_  
  temporarily change player's permissions  

  This can be used to temporarily change player's permissions.
  Changes are only done in-memory, so these will revert if the
  disconnects or the server reloads.
  You can specify a _permission_ and it will show it's valueor
  if true|false is specified it will be changed.
  If you specify **dump**, it will show all permissions
  associated to a player.

* **players**  
  Shows what players are on-line  
* **pluginmgr** _&lt;enable|disable|reload|info|commands|permissions|load&gt;_ _plugin&gt;  
  manage plugins  

  Manage plugins.
  The following sub-commands are available:
  - **pluginmgr** **enable** _<plugin>_
    - Enable a disabled plugin.
  - **pluginmgr** **disable** _<plugin>_
    - Disables an enabled plugin.
  - **pluginmgr** **reload** _<plugin>_
    - Disables and enables a plugin.
  - **pluginmgr** **info** _<plugin>_
    - Show plugin details
  - **pluginmgr** **commands** _<plugin>_
    - Show commands registered by plugin
  - **pluginmgr** **permissions** _<plugin>_
    - Show permissions registered by plugin
  - **pluginmgr** **load** _<path>_
    - Load a plugin from file path (presumably outside the **plugin** folder.)

* **poptp**  
  Returns to the previous location  

* **prefix** _[-n]_ _&lt;prefix text&gt;_  
  prepend prefix to chat lines  

  This allows you to prepend a prefix to chat lines.
  To stop enter `/prefix` by itself (or `prefix` at the console).
  Usage examples:

  - Send multiple `/as player` commands in a row.
  - Start a private chat `/tell player` with another player.
  - You prefer commands over chat: `-n /`

* **pushtp** _&lt;player&gt;_ _[target]_  
  Saves current location and teleport  
* **rcon** **[--add|--rm|--ls|id]** _&lt;command&gt;_  
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

* **reg** _[subcommand]_ _[options]_  
  Manage player registrations  

  By default it will show the number of registered players.  The following
  sub-commands are available:
  - **count**
    - default sub-command.  Counts the number of registered players.
  - **list** _[pattern]_
    - Display a list of registered players or those that match the
      wildcard _pattern_.
  - **rm** _<player>_
    - Removes _<player>_ registration.
  - **since** _<when>_
			- Display list of players registered since a date/time.
* **rpt** [_message_|**read|clear** _&lt;all|##&gt;_]  
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

* **seearmor** _&lt;player&gt;_  
  Show player's armor  

* **seeinv** _&lt;player&gt;_  
  Show player's inventory  
* **servicemode** **[on|off** _[message]_ **]**  
  controls servicemode  

  If `on` it will activate service mode.  In service mode new
  players can not join (unless they are ops).  Existing players
  can remain but may be kicked manually by any ops.

* **setarmor** _[player]_ _[piece]_ _&lt;type&gt;_  
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
* **skin** _[player]_ _[save|load|ls]_ _[name]_  
  manage player's skins  

  Manipulate player's skins on the server.
  Sub-commands:
  - **skin** **ls**
    - List all available skins on the server.  Default command.
  - **skin** _[player]_ **save** _<name>_
    - Saves _player_'s skin to _name_.
  - **skin** _[player]_ **load** _<name>_
    - Loads _player_'s skin from _name_.
* **slay** _&lt;player&gt;_ _[msg]_  
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
* **summon** _&lt;player&gt;_ _[message]_  
  Summons a player to your location  
* **throw** _&lt;player&gt;_ _[force]_  
  Throw a player in the air  
* **whois** _&lt;player&gt;_  
  Gives detail information on players  


Commands scheduled by `at` and `after` will only run as
long as the server is running.  These scheduled commands will *not*
survive server reloads or reboots.


### Module reference

#### blood-particles

Display particles when a player gets hit

#### broadcast-ft

Broadcast player's using FastTransfer

This listener module will broadcast when a player uses FastTransfer

#### broadcast-tp

Broadcast player's teleports

This listener module will broadcast when a player teleports to
another location.

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

#### broadcast-tp

*  world: world broadcast setting. If true, will broadcast teleports accross worlds.
*  local: local broadcast setting. This will broadcast teleports that go beyond this number.

#### features


This section you can enable/disable commands and listener modules.
You do this in order to avoid conflicts between different
PocketMine-MP plugins.  It has one line per feature:

   feature: true|false

If `true` the feature is enabled.  if `false` the feature is disabled.

#### freeze-thaw

*  hard-freeze: how hard to freeze players. If `true` no movement is allowed.  If `false`, turning is allowed but not walking/running/flying, etc.

#### join-mgr

*  adminjoin: broadcast whenever an op joins
*  servermotd: show the server's motd when joining

#### rcon-client


This section configures the rcon client connections.  You can configure
this section through the *rcon* command.


### Permission Nodes

* gb.module.repeater : Access to repeater module
* gb.cmd.players : allow players command
* gb.cmd.ops : list server ops
* gb.cmd.sudo : Allow to run command as another user
  (Defaults to Op)
* gb.cmd.gma : Allow to switch gamemode to Adventure
  (Defaults to Op)
* gb.cmd.gms : Allow to switch gamemode to survival
  (Defaults to Op)
* gb.cmd.gmc : Allow to switch gamemode to creative
  (Defaults to Op)
* gb.cmd.gmspc : Allow to switch gamemode to spectator
  (Defaults to Op)
* gb.cmd.slay : Allow slaying players
  (Defaults to Op)
* gb.cmd.heal : Allow healing
  (Defaults to Op)
* gb.cmd.whois : Show player details
  (Defaults to Op)
* gb.cmd.whois.showip : Show player IP address
  (Defaults to Op)
* gb.cmd.timings : Show timings data
  (Defaults to Op)
* gb.cmd.seearmor : Show player's armor
  (Defaults to Op)
* gb.cmd.seeinv : Show player's inventory
  (Defaults to Op)
* gb.cmd.clearinv : Clear player's inventory
* gb.cmd.clearinv.others : Clear other's inventory
  (Defaults to Op)
* gb.cmd.clearhotbar : Clear player's hotbar
* gb.cmd.clearhotbar.others : Clear other's hotbar
  (Defaults to Op)
* gb.cmd.get : Get blocks
  (Defaults to Op)
* gb.cmd.shield : Allow players to become invulnerable
  (Defaults to Op)
* gb.cmd.servicemode : Allow access to service mode command
  (Defaults to Op)
* gb.servicemode.allow : Allow login when in service mode.
  (Defaults to Op)
* gb.cmd.opms : Allow to send op only messages
* gb.cmd.entities : Access entities command
  (Defaults to Op)
* gb.cmd.mute : mute/unmute
  (Defaults to Op)
* gb.cmd.freeze : freeze/thaw users
  (Defaults to Op)
* gb.cmd.after : Schedule commands
  (Defaults to Op)
* gb.cmd.rpt : Report issues
* gb.cmd.rpt.read : Read reported issues
  (Defaults to Op)
* gb.cmd.summon : Summon|Dismiss command
  (Defaults to Op)
* gb.cmd.pushpoptp : push/pop teleport
  (Defaults to Op)
* gb.cmd.prefix : Allow the use of /prefix
* gb.cmd.spawn : Allow to teleport to spawn
* gb.cmd.burn : Allow the use of burn command
  (Defaults to Op)
* gb.cmd.throw : Allow to throw players up in the air
  (Defaults to Op)
* gb.cmd.blowup : Allow to blow-up players
  (Defaults to Op)
* gb.cmd.setarmor : Allow you to set your armor
  (Defaults to Op)
* gb.cmd.setarmor.others : Allow you to set others armor
  (Defaults to Op)
* gb.cmd.spectator : Turn players into spectators
  (Defaults to Op)
* gb.cmd.follow : Let players can follow others
  (Defaults to Op)
* gb.cmd.followme : Make players follow you
  (Defaults to Op)
* gb.cmd.rcon : use RCON client
  (Defaults to Op)
* gb.cmd.rcon.config : Modify the RCON configuration
  (Defaults to Op)
* gb.cmd.fly : Flight control
  (Defaults to Op)
* gb.cmd.crash : Crash dump management
  (Defaults to Op)
* gb.cmd.pluginmgr : Manage plugins
  (Defaults to Op)
* gb.cmd.permmgr : Change permissions
  (Defaults to Op)
* gb.cmd.regs : Manage player registrations
  (Defaults to Op)
* gb.cmd.skin : Manage skins
  (Defaults to Op)
* gb.cmd.skin.other : Manage other's skins skins
  (Defaults to Op)
* gb.cmd.invisible : make player invisible
  (Defaults to Op)
* gb.cmd.invisible.inmune : make player inmune to invisibility tricks
  _(Defaults to disabled)_


## Translations

This plugin will honour the server language configuration.  The
languages currently available are:

* English
* Spanish

You can provide your own message file by creating a file called
`messages.ini` in the pluginc config directory.  Check
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/GrabBag)
for sample files.

# Changes

* 2.2.2
  * Default permission for /spawn changed from op to everyone.
  * Whois shows clientId
  * CmdAfterAt: list tasks and cancel them
  * Sounds on teleport
  * CmdRegMgr : show players registering since certain date
* 2.2.1: Misc fixes and new features
  * New Command:
    * skin : manipulate skins
    * invis : make player invisible
  * Fixes:
    * CmdAs : Fixed chat (for PM1.5)
    * CmdPlayers : Show name instead of displayname
    * CmdClearInv : Fixed checking permissions for others
* 2.2.0: Another update
  * CmdWhois : Works with off-line players and also returns SimpleAuth
    details.
  * CmdAfterAt : It nows work with fractional seconds
  * New Commands:
    * perm : temporarily change permissions
    * reg : Manage player registration
  * New module:
    * broadcast-ft: Like broacast-tp but for fast transfers
  * Translation fixes, and other typos
  * blood-particle: it also shows dust when the player dies
  * broadcast-tp: show particles
* 2.1.0 : Regular update
  * New commands:
    * gmspc - spectator mode
    * fly - enables flying for players in survival
    * clearhotbar - clear your hotbar
    * PluginMgr - manage plugins
    * Crash - manage crash dumps
  * New modules:
    * broadcast-tp : Broadcast when a player teleports
    * blood-particles : blood particles (actually redstone) when
      players get hit.
  * Command updates:
    * clearinv - can clear your own inventory
    * whois - returns more information
  * Switched to my common library.
  * Added translation, Spanish.
* 2.0.1: CallbackTasks
  * Removed CallbackTask deprecation warnings
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

