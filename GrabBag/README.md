
<img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/GrabBag-icon.png" style="width:64px;height:64px" width="64" height="64"/>

<!-- meta:Categories = General -->
<!-- meta:PluginAccess = Internet Services, Other Plugins, Manages Permissions, Commands, Data Saving, Entities, Tile Entities, Manages Plugins -->

<!-- template: gd2/header.md -->
<!-- end-include -->

## Overview

<!-- php: $v_forum_thread = "http://forums.pocketmine.net/threads/grabbag.7524/"; -->
<!-- template: prologue.md -->
<!-- end-include -->

A miscellaneous collection of commands and listener modules.  **All**
features can be configured to be _disabled|enabled_ so as to co-exist
with other plugins.

This plugin is focused more towards commands to help system
administration.

<!-- php:$h=3; -->
<!-- template: gd2/cmdoverview.md -->
<!-- end-include -->

### Modules

<!-- template: gd2/modovw.md -->
<!-- end-include -->

## Documentation

This plugin collects a number of commands and listener plugins that I
find useful and wanted to have in a single plugin rather through
multiple ones.  The available commands and listener modules can be
configured.  This allows this plugin to co-exist peacefully with other
plugins.

### Command Reference

The following commands are available:

<!-- template: gd2/subcmds.md -->
<!-- end-include -->

<!-- snippet: cmdnotes  -->
<!-- end-include -->

### Module reference

<!-- php:$h=4; -->
<!-- template: gd2/mods.md -->
<!-- end-include -->

<!-- template: test.md -->
<!-- end-include -->

### Command Selectors
<!-- snippet: cmdselector  -->
<!-- end-include -->

### API

Since version 2.3 of GrabBag, an API is available.  For more information
see the
[API documentation.](http://alejandroliu.github.io/pocketmine-plugins/apidocs/index.html)

### Configuration

Configuration is through the **config.yml** file.
The following sections are defined:

<!-- php:$h=4; -->
<!-- template: gd2/cfg.md -->
<!-- end-include -->

### Permission Nodes

<!-- snippet: rtperms -->
<!-- end-include -->

<!-- template: gd2/mctxt.md -->
<!-- end-include -->

## Additional Libraries

The following third party libraries are included:

* [xPaw's MinecraftQuery](http://xpaw.me GitHub: https://github.com/xPaw/PHP-Minecraft-Query)

## WIP and issues

* Query:
  * Queries are done in the main thread.  Should be moved as an AsyncTask.
  * Queries to the same server do not work.
  * Queries not using QueryDaemon
* Deprecated modules:
  - CmdSpectator

# Changes

* 2.4.0: libcommon bundle
  * Query/Ping Daemon functionality
  * Server configuration can have alternative IPs
  * Tp Request and Homes (Requested by @rvachvg)
  * New Commands:
    * teleport: warps, top, back
    * informational: near, iteminfo, xyz
    * inventory: fixit, plenty
    * player mgmt: afk
    * Developer tools: echo, rem, expand, onevent, pmscript/rc, trace
    * Server management: wall
  * JoinMgr: reserved slots, initial armor and items, always spawn
  * New modules: custom-death and merge-slots
  * CmdSpectator is now deprecated
  * libcommon is embedded and usable.  Replaces a separate libcommon library.
* 2.3.0: Update, new functionality and API
  * Plugin loader will check server paths (include plugin folder)
  * New plugin mgr sub command to dump messages.ini and to un-install.
  * Doc updates.
  * Updating libcommon stuff.
  * WhoIs command now supports [PurePerms](https://forums.pocketmine.net/plugins/pureperms.862/)
  * Added aliases module
  * New command rminv (@SeangJemmy)
  * New reop command
  * Configuration of Rcon and Query has changed
  * Permissions are conditionally created (if the module is enabled)
  * Adding a complete new API
* 2.2.7:
  * Minor fix in Reg command
  * Fixed bug in Command Selector (@Legoboy0215 and @SM11)
  * Command selector: w=world (@SM11)
* 2.2.6: Bugfix
  * gift command fixing
  * ChatMgr crash fixing
  * (@SM11) Edit tiles by location (instead of by id)
* 2.2.5:
  * Fixed bugs
  * CmdSelMgr: configure max
  * gift command / broadcast
  * Switched MPMU::itemName for ItemName::str
* 2.2.4:
  * Re-formatted Queries (Requested by @Daniel123)
  * FollowMgr check if player is flying before teleporting.
  * Added /nick command (ChatMgr)
  * Added Command Selector.
* 2.2.3: Multi-server
  * Chat manager (Requested by @CaptainKenji17)
  * Must have multi-server feactures:
    * Query command: let's you get info from servers in your network, like
      - number of servers on-line, number of players
      - show what players are on-line and on which servers
    * Rcon: let's you execute commands on remote servers using Rcon.  The
      new --all switch lets you send the same command to all the servers in
      your network.
* 2.2.2:
  * Default permission for /spawn changed from op to everyone.
  * Whois shows clientId
  * CmdAfterAt: list tasks and cancel them
  * Sounds on teleport
  * CmdRegMgr : show players registering since certain date
  * Transfer broadcast : fixed bug
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

<!-- php:$copyright="2015"; -->
<!-- template: gd2/gpl2.md -->
<!-- end-include -->
