<img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/GrabBag-icon.png" style="width:64px;height:64px" width="64" height="64"/>

<!-- meta:Categories = General -->
<!-- meta:PluginAccess = Internet Services, Other Plugins, Manages Permissions, Commands, Data Saving, Entities, Tile Entities, Manages Plugins -->

<!-- template: gd2/header.md -->

# GrabBag

- Summary: Collection of miscellaneous commands and listener modules
- PocketMine-MP version: 1.4 (API:1.10.0), 1.4.1 (API:1.11.0), 1.5 (API:1.12.0), 1.6+php7 (API:2.0.0)
- DependencyPlugins: 
- OptionalPlugins: FastTransfer
- Categories: General 
- Plugin Access: Internet Services, Other Plugins, Manages Permissions, Commands, Data Saving, Entities, Tile Entities, Manages Plugins 
- WebSite: https://github.com/alejandroliu/pocketmine-plugins/tree/master/GrabBag

<!-- end-include -->

## Overview

<!-- php: $v_forum_thread = "http://forums.pocketmine.net/threads/grabbag.7524/"; -->
<!-- template: prologue.md -->

**DO NOT POST QUESTIONS/BUG-REPORTS/REQUESTS IN THE REVIEWS**

It is difficult to carry a conversation in the reviews.  If you
have a question/bug-report/request please use the
[Thread](http://forums.pocketmine.net/threads/grabbag.7524/) for
that.  You are more likely to get a response and help that way.

_NOTE:_

This documentation was last updated for version **2.5.0**.

Please go to
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/GrabBag)
for the most up-to-date documentation.

You can also download this plugin from this [page](https://github.com/alejandroliu/pocketmine-plugins/releases/tag/GrabBag-2.5.0).

<!-- end-include -->

A miscellaneous collection of commands and listener modules.  **All**
features can be configured to be _disabled|enabled_ so as to co-exist
with other plugins.  Furthermore, permissions are added on the fly when
features are enabled, so if you only enable a few features, your permission
list will not be very long.

<!-- php:$h=3; -->
<!-- template: gd2/cmdoverview.md -->

### Developer Tools

* echo: shows the given text
* expand: Shows the given text after handling variable expansions
* onevent: Run command on event
* rc: Runs the given script
* rem: Do nothing
* trace: controls event tracing

### Entity Management

* entities: entity management

### Informational

* near: Shows what players are near by
* ops: Shows who are the ops on this server.
* players: Shows what players are on-line
* showtimings: Shows timing reports as reported by **timings**
* whois: Gives detailed information on players
* xyz: shows the players position and bearing

### Inventory Management

* clearhotbar: Clear player's hotbar
* clearinv: Clear player's inventory
* fixit: Fix item being held
* get: obtain an item
* gift: give an item to a player
* iteminfo: Show additional info on item held
* plenty: When in survival, make sure that a player never runs out of items
* rminv: Remove item from player's Inventory
* seearmor: Show player's armor
* seeinv: Show player's inventory
* setarmor: Sets armor (even in creative)

### Player Management

* afk: Toggles AFK status
* as: run command as somebody else
* clearchat: Clears your chat window
* fly: Toggle flying **ONLY FOR PM >1.5**
* gma: Change your gamemode to _Adventure_.
* gmc: Change your gamemode to _Creative_.
* gms: Change your gamemode to _Survival_.
* gmspc: Change your gamemode to _Spectator_.
* invis: makes player invisible
* nick: Change your display name
* perm: temporarily change player's permissions
* prefix: prepend prefix to chat lines
* reg: Manage player registrations
* reop: Let ops drop priviledges temporarily
* shield: player is protected from taking damage
* skin: manage player's skins
* spy: spy on a player in-game (chat-scribe)

### Server Management

* after: schedule command after a number of seconds
* alias: Create a new command alias
* at: schedule command at an appointed date/time
* crash: manage crash dumps
* log: toggle server logging (chat-scribe)
* opms: sends a message to ops only
* pluginmgr: manage plugins
* query: query remote servers
* rcon: rcon client
* rpt: report an issue to ops
* servers: Manage peer server connections
* servicemode: controls servicemode
* wall: shows the given text to all servers

### Teleporting

* delhome: Removes your home
* delwarp: Removes warp
* dismiss: Dismiss a previously summoned player
* follow: Follow a player
* follow-off: stop following a player
* followers: List who is following who
* followme: Make a player follow you
* followme-off: stop making a player follow you
* ftserver: Travel to remove servers
* home: Teleport to home
* poptp: Returns to the previous location
* pushtp: Saves current location and teleport
* sethome: Sets your home location
* setwarp: Sets warp location
* shield: player is teleported to the place of last death
* spawn: Teleport player to spawn point
* summon: Summons a player to your location
* top: Teleport player to the top
* tpaccept: Accept a Teleport request
* tpahere: Ask a player to teleport to you
* tpask: Ask a player to teleport to them
* tpdecline: Decline a teleport request
* warp: Teleport to warp.

### Trolling

* blowup: explode a player
* burn: Burns the specified player
* chat-on|chat-off: Allow players to opt-out from chat
* freeze|thaw: freeze/unfreeze a player so they cannot move.
* heal: Restore health to a player
* mute|unmute: mutes/unmutes a player so they can not use chat
* slay: Kills the specified player
* spectator|unspectator: toggle a player's spectator mode **(DEPRECATED)**
* throw: Throw a player in the air


<!-- end-include -->

### Modules

<!-- template: gd2/modovw.md -->
* blood-particles: Display particles when a player gets hit
* broadcast-ft: Broadcast player's using FastTransfer
* broadcast-tp: Broadcast player teleports
* cmd-selector: Implements "@" command prefixes
* custom-death: Customize what happens when a player dies
* join-mgr: Customize players when they joins the server
* mega-slots: Merges the slot count of multiple servers
* motd-task: Background task to ping configured remote servers
* query-task: Background task to query configured remote servers
* repeater: Uses **!!** to repeat command with changes

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
* afk: Toggles AFK status<br/>
   usage: **afk** _[message]_
  
  Implements basic Away From Key functionality.  This is actually
  implemented on the basis of the **freeze-thaw**, **mute-unmute** and
  **shield** modules.  These have to be active for this command to work.
* after: schedule command after a number of seconds<br/>
  usage: **after** _&lt;seconds&gt;_ _&lt;command&gt;|list|cancel_ _&lt;id&gt;_
  
  Will schedule to run *command* after *seconds*.
  The **list** sub command will show all the queued commands.
  The **cancel** sub command allows you to cancel queued commands.
  
* alias: Create a new command alias<br/>
  usage: **alias** **[-f]** _&lt;alias&gt;_ _&lt;command&gt;_ _[options]_
  
  Create an alias to a command.
  Use the **-f** to override existing commands
  
  Examples:
  - alias -f list query list
  - alias -f help echo You are beyond help!
  - alias fancycmd rc fancycmd
* as: run command as somebody else<br/>
  usage: **as** _&lt;player&gt;_ _&lt;command&gt;_
* at: schedule command at an appointed date/time<br/>
  usage: **at** _&lt;time&gt;_ _[:]_ _&lt;command&gt;|list|cancel _&lt;id&gt;_
  
  Will schedule to run *command* at the given date/time.  This uses
  php's [strtotime](http://php.net/manual/en/function.strtotime.php)
  function so _times_ must follow the format described in
  [Date and Time Formats](http://php.net/manual/en/datetime.formats.php).
  The **list** sub command will show all the queued commands.
  The **cancel** sub command allows you to cancel queued commands.
  
* blowup: explode a player<br/>
  usage: **blowup** _&lt;player&gt;_ _[yield]_ **[magic]** **[normal]**
  
  Explodes _player_ with an explosion with the given _yield_ (a number).
  If **magic** is specified no damage will be taken by blocks.  The
  default is **normal**, where blocks do get damaged.
* burn: Burns the specified player<br/>
  usage: **burn** _&lt;player&gt;_ _[secs]_
  
  Sets _player_ on fire for the specified number of seconds.
  Default is 15 seconds.
  
* chat-on|chat-off: Allow players to opt-out from chat<br/>
  usage: **chat-on|chat-off** _[player|--list|--server]_
  
  Prevents players from sending/receiving chat messages.
  The following options are recognized:
  - --list : Lists the players that have chat on/off status
  - --server : Globally toggles on/off chat.
  
* clearchat: Clears your chat window<br/>
  usage: **clearchat**
  
* clearhotbar: Clear player's hotbar<br/>
  usage: **clearhotbar** _[player]_
* clearinv: Clear player's inventory<br/>
  usage: **clearinv** _[player]_
* crash: manage crash dumps<br/>
  usage: **crash** _[ls|clean|show]_
  
  Will show the number of **crash** files in the server.
  The following optional sub-commands are available:
  - **crash** **count**
    - Count the number of crash files
  - **crash** **ls** _[pattern]_
    - List crash files
  - **crash** **clean** _[pattern]_
    - Delete crash files
  - **show** _[pattern]_
    - Shows the crash file ##
* delhome: Removes your home<br/>
  usage: **delhome**
* delwarp: Removes warp<br/>
  usage: **delhome** _&lt;warpname&gt;_
* dismiss: Dismiss a previously summoned player<br/>
  usage: **dismiss** _&lt;player&gt;_ _[message]_
* echo: shows the given text<br/>
   usage: **echo** _[text]_
* entities: entity management<br/>
  usage: **entities** _[subcommand]_ _[options]_
  
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
     - Clear entities from the server.
  
  Additionally, tiles can be specified by providing the following:
  
  - t(x),(y),(z)[,world]
* expand: Shows the given text after handling variable expansions<br/>
  usage: **expand** _[text]_
* fixit: Fix item being held<br/>
  usage: **fixit** _[player]_
* fly: Toggle flying **ONLY FOR PM >1.5**<br/>
  usage: **fly**
* follow: Follow a player<br/>
  usage: **follow** _&lt;player&gt;_
* follow-off: stop following a player<br/>
  usage: **follow-off**
* followers: List who is following who<br/>
  usage: **followers**
* followme: Make a player follow you<br/>
  usage: **folowme** _&lt;player&gt;_
* followme-off: stop making a player follow you<br/>
  usage: **followme-off** _&lt;player&gt;_
* freeze|thaw: freeze/unfreeze a player so they cannot move.<br/>
  usage: **freeze|thaw** [ _player_ | **--hard|--soft** ]
  
  Stops players from moving.  If no player specified it will show
  the list of frozen players.
  
  If **--hard** or **--soft** is specified instead of a player name, it
  will change the freeze mode.
* ftserver: Travel to remove servers<br/>
  usage: **ftserver** _&lt;serverid&gt;_
  
  Teleport to servers defined with the **/servers** command.
* get: obtain an item<br/>
  usage: **get** _&lt;item&gt;_ _[count]_
  
  This is a shortcut to **give** that lets player get items for
  themselves.  You can replace **item** with **more** and the
  current held item will be duplicated.
* gift: give an item to a player<br/>
  usage: **gift** _[player]_ _&lt;item&gt;_ _[count]_
  
  This is a re-implementation of **give** command.
* gma: Change your gamemode to _Adventure_.<br/>
  usage: **gma**
* gmc: Change your gamemode to _Creative_.<br/>
  usage: **gmc**
* gms: Change your gamemode to _Survival_.<br/>
  usage: **gms**
* gmspc: Change your gamemode to _Spectator_.<br/>
  usage: **gmspc**
* heal: Restore health to a player<br/>
  usage: **heal** _[player]_ _[ammount]_
  
  Heals a player.  If the amount is positive it will heal, if negative
  the player will be hurt.  The units are in 1/2 hearts.
* home: Teleport to home<br/>
  usage: **home**
* invis: makes player invisible<br/>
  usage: **invis**
  This will toggle your invisibility status.
* iteminfo: Show additional info on item held<br/>
  usage: **iteminfo**
* log: toggle server logging (chat-scribe)<br/>
  usage: **log** **[on|off]**
  
  without arguments will return the logging mode.  Otherwise **on** will
  enable logging, **off** will disable logging.
* mute|unmute: mutes/unmutes a player so they can not use chat<br/>
  usage: **mute|unmute** _[player]_
  
  Stops players from chatting.  If no player specified it will show
  the list of muted players.
* near: Shows what players are near by<br/>
  usage: **near** _[radius]_
* nick: Change your display name<br/>
  usage: **nick** _&lt;name&gt;_
* onevent: Run command on event<br/>
  usage: usage: **onevent** _&lt;event&gt;_ _[cmd]_
  
  This command will make it so a command will be executed whenever an
  event is fired.  Options:
  * **onevent**
    - show registered events
  * **onevent** _&lt;event&gt;_
    - Show what command will be executed.
  * **onevent** _<event>_ _<command>_
    - Will schedule for _command_ to be executed.
  * **onevent** _<event>_ **--none**
    - Will remove the given event handler
  
* opms: sends a message to ops only<br/>
  usage: **opms** _[msg]_
  
  Sends chat messages that are only see by ops.  Only works with ops
  that are on-line at the moment.  If you no ops are on-line you
  should use the **rpt** command.
* ops: Shows who are the ops on this server.<br/>
  usage: **ops**
* perm: temporarily change player's permissions<br/>
  usage: **perm** _&lt;player&gt;_ _&lt;dump|permission&gt;_ _[true|false]_
  
  This can be used to temporarily change player's permissions.
  Changes are only done in-memory, so these will revert if the
  disconnects or the server reloads.
  You can specify a _permission_ and it will show it's value or
  if true|false is specified it will be changed.
  If you specify **dump**, it will show all permissions
  associated to a player.
* players: Shows what players are on-line<br/>
  usage: **players**
* plenty: When in survival, make sure that a player never runs out of items<br/>
  usage: **plenty**
  
  When **plenty** is on, the player will not run out of items.  Whenever
  the current block being placed is about to run out, you are given
  new blocks of the same type automatically.
* pluginmgr: manage plugins<br/>
  usage: **pluginmgr** _&lt;subcmd&gt;_ _&lt;plugin&gt;_
  
  Manage plugins.
   The following sub-commands are available:
  - **pluginmgr** **enable** _&lt;plugin&gt;_
      - Enable a disabled plugin.
  - **pluginmgr** **disable** _&lt;plugin&gt;_
      - Disables an enabled plugin.
  - **pluginmgr** **reload** _&lt;plugin&gt;_
      - Disables and enables a plugin.
  - **pluginmgr** **info** _&lt;plugin&gt;_
      - Show plugin details
  - **pluginmgr** **commands** _&lt;plugin&gt;_
      - Show commands registered by plugin
  - **pluginmgr** **permissions** _&lt;plugin&gt;_
      - Show permissions registered by plugin
  - **pluginmgr** **load** _&lt;path&gt;_
      - Load a plugin from file path (presumably outside the **plugin** folder.)
  - **pluginmgr** **dumpmsg** _&lt;plugin&gt;_ _[lang]_
      - Dump messages.ini.
  - **pluginmgr** **uninstall** _&lt;plugin&gt;_
      - Uninstall plugin.
  - **pluginmgr** **feature** _&lt;plugin&gt;_ _[[-|+]feature]_
      - For plugins that have a _features_ table in **config.yml**
        this will let you change those settings.
* poptp: Returns to the previous location<br/>
  usage: **poptp**
* prefix: prepend prefix to chat lines<br/>
  usage: **prefix** _[-n]_ _&lt;prefix text&gt;_
  
  This allows you to prepend a prefix to chat lines.
  To stop enter **/prefix** by itself (or **prefix** at the console).
  Usage examples:
  
  - Send multiple **/as player** commands in a row.
  - Start a private chat **/tell player** with another player.
  - You prefer commands over chat: **/prefix -n /**
  
  When prefix is enabled and you one to send just _one_ command without
  prefix, prepend your text with **<**.
* pushtp: Saves current location and teleport<br/>
  usage: **pushtp** _&lt;player&gt;_ _[target]_
* query: query remote servers<br/>
  usage: **query** **[list|info|plugins|players|summary]** _[opts]_
  
  This is a query client that you can use to query other
  remote servers.
  
  Servers are defined with the **servers** command.
  
  Options:
  - **query list**
      - List players on all configured `query` connections.
  - **query info** _&lt;id&gt;_
      - Return details from query
  - **query players** _&lt;id&gt;_
      - Return players on specified server
  - **query plugins** _&lt;id&gt;_
      - Returns plugins on specified server
  - **query summary**
      - Summary of server data
* rc: Runs the given script<br/>
  usage: **rc** _&lt;script&gt;_ _[args]_
  
  This command will execute PMScripts present in the **GrabBag**
  folder.  By convention, the ".pms" suffix must be used for the file
  name, but the ".pms" is ommitted when issuing this command.
  
  The special script **autostart.pms** is executed automatically
  when the **GrabBag** plugin gets enabled.
  
  By default only scripts in the Plugin directory are executed.
  You can disable this feature with the command:
  
       rc --no-limit-path
  
  To resume limiting:
  
       rc --limit-path
  
* rcon: rcon client<br/>
  usage: **rcon** _&lt;id&gt;_ _&lt;command&gt;_
  
  This is an rcon client that you can used to send commands to other
  remote servers identified by **id**.
  
  You can specify multiple targets by separating with commas (,).
  Otherwise, you can use **--all** keyword for the _id_ if you want to
  send the commands to all configured servers.
  
  Use the **servers** command to define the rcon servers.
* reg: Manage player registrations<br/>
  usage: **reg** _[subcommand]_ _[options]_
  
  By default it will show the number of registered players.  The following
  sub-commands are available:
  - **count**
    - default sub-command.  Counts the number of registered players.
  - **list** _[pattern]_
    - Display a list of registered players or those that match the
      wildcard _pattern_.
  - **rm** _&lt;player&gt;_
    - Removes _player_ registration.
  - **since** _&lt;when&gt;_
    - Display list of players registered since a date/time.
* rem: Do nothing<br/>
  usage: **rem** _[text]_
* reop: Let ops drop priviledges temporarily<br/>
  usage: **reop** [_player_]
  
  Will drop **op** priviledges from player.  Player can get **op**
  back at any time by enter **reop** again or by disconnecting.
* rminv: Remove item from player's Inventory<br/>
  usage: **rminv** _[player]_ _&lt;item&gt;_ _[quantity]_
* rpt: report an issue to ops<br/>
  usage: **rpt** [_message_|**read|clear** _&lt;all|##&gt;_]
  
  Logs/reports an issue to server ops.  These issues are stored in a
  a file which can be later read by the server operators.  Use this
  when there are **no** ops on-line.  If there are ops on-line you
  should use the **opms** command.
  
  The following ops only commands are available:
  - **rpt** **read** _[##]_
    - reads reports.  You can specify the page by specifying a number.
  - **rpt** **clear** _&lt;all|##&gt;_
    - will delete the specified report or if **all**, all the reports.
* seearmor: Show player's armor<br/>
  usage: **seearmor** _&lt;player&gt;_
* seeinv: Show player's inventory<br/>
  usage: **seeinv** _&lt;player&gt;_
* servers: Manage peer server connections<br/>
  usage: **servers** **&lt;add|rm|ls&gt;** _[options]_
  
  This is used to manage the peer server definitions used by the
  **RCON** and **QUERY** modules, among others.
  
  Sub-commands:
  - **servers add** _&lt;id&gt;_ _&lt;host&gt;_ _[port]_ _[options]_ _[# comments]_
    - adds a new connection with **id**
  - **servers rm** _&lt;id&gt;_
    - Removes peer **id**.
  - **servers ls**
    - List configured peers.
  - **servers info** _&lt;id&gt;_
    - Show server details
  Avalable options (when adding servers):
  * rcon-port=port : Alternative port for RCON
  * rcon-pw=secret : RCON password
  * rcon-host="str" : Alternative host for RCON
  * no-motd-task : This server will not be polled by the MOTD Daemon
  * no-query-task : This server will not be polled by the Query Daemon
  * query-use-ipv4 : Resolve host name when doing queries
  * query-use-ipv4 : Resolve host name when doing queries
  * query-host=host : Alternative host to be used in queries
  * ping-host=host : Alternative host to be used in MOTD pings
  * ping-use-ipv4 : Ping host by IP address
  * ft-host=host : Alternative host to be used in fast transfer
* servicemode: controls servicemode<br/>
  usage: **servicemode** **[on|off** _[message]_ **]**
  
  If **on** it will activate service mode.  In service mode new
  players can not join (unless they are ops).  Existing players
  can remain but may be kicked manually by any ops.
* setarmor: Sets armor (even in creative)<br/>
  usage: **setarmor** _[player]_ _&lt;quality|item&gt;_
  
  This command lets you armor up.  It can armor up creative players too.
  If no **player** is given, the player giving the command will be armored.
  
  Quality can be one of **none**, **leather**, **chainmail**, **iron**,
  **gold** or **diamond**.  This will make all armor components of that
  quality.
  
  Otherwise you can specify an armor item, and this will be placed in
  your armor slot.
  
* sethome: Sets your home location<br/>
  usage: **sethome**
* setwarp: Sets warp location<br/>
  usage: **setwarp** _&lt;warpname&gt;_ _[x,y,z[:world]]_
* shield: player is protected from taking damage<br/>
  usage: **shield**
  
  This will toggle your shield status.
* shield: player is teleported to the place of last death<br/>
  usage: **back**
* showtimings: Shows timing reports as reported by **timings**<br/>
  usage: **timings** _[t#]_
  
  If nothing specified it will list available reports.  These are
  of the form of **timings.txt** or `timings1.txt`.
  
  To specify a report enter **t** for **timings.txt** or **t1** for
  **timings1.txt**.
* skin: manage player's skins<br/>
  usage: **skin** _[player]_ _[save|load|ls]_ _[name]_
  
  Manipulate player's skins on the server.
  Sub-commands:
  - **skin** **ls**
      - List all available skins on the server.  Default command.
  - **skin** _[player]_ **save** _&lt;name&gt;_
      - Saves _player_'s skin to _name_.
  - **skin** _[player]_ **load** _[--slim]_ _&lt;name&gt;_
      - Loads _player_'s skin from _name_.
  - **skin** _[player]_ **slim**
      - Make player's skin slim
  - **skin** _[player]_ **thick**
      - Make player's skin non-slim
  - **skin** **formats**
      - Show supported formats
* slay: Kills the specified player<br/>
  usage: **slay** _&lt;player&gt;_ _[messsage]_
  Kills a player with an optional _message_.
* spawn: Teleport player to spawn point<br/>
  usage: **spawn**
* spectator|unspectator: toggle a player's spectator mode **(DEPRECATED)**<br/>
  usage: **spectator|unspectator** _[player]_
  
  This command will turn a player into an spectator.  In this mode
  players can move but not interact (i.e. can't take/give damage,
  can't place/break blocks, etc).
  
  If no player was specified, it will list spectators.
* spy: spy on a player in-game (chat-scribe)<br/>
  usage: **spy** **[start|stop|status]** _[player]_
  
  This command is useful for a help-desk type function.  Let's you locale_lookup
  over the shoulder of a player and see what commands they are entering.
* summon: Summons a player to your location<br/>
  usage: **summon** _&lt;player&gt;_ _[message]_
* throw: Throw a player in the air<br/>
  usage: **throw** _&lt;player&gt;_ _[force]_
* top: Teleport player to the top<br/>
  usage: **top** _[player]_
* tpaccept: Accept a Teleport request<br/>
  usage: **tpaccept** _&lt;player&gt;_
* tpahere: Ask a player to teleport to you<br/>
  usage: **tpahere** _&lt;player&gt;_
* tpask: Ask a player to teleport to them<br/>
  usage: **tpask** _&lt;player&gt;_
* tpdecline: Decline a teleport request<br/>
  usage: **tpdecline** _&lt;player&gt;_
* trace: controls event tracing<br/>
   usage: **trace** _[options]_
  
  Trace will show to the user the different events that are being
  triggered on the server.  To reduce spam, events are de-duplicated.
  
  Sub commands:
  * **trace**
    - Shows the current trace status
  * **trace** **on**
    - Turns on tracing
  * **trace** **off**
    - Turns off tracing
  * **trace** **events** _[type|class]_
    - Show the list of the different event types and classes.  If a _type_
      or _class_ was specified, it will show the events defined for them.
  * **trace** _&lt;event|type|class&gt;_ _[additional options]_
    - Will add the specified _event|type|class_ to the current user's
      trace session.
  * **trace** _&lt;-event|type|class&gt;_ _[additional options]_
    - If you start the _event|type|class_ specification name with a
      **dash**, the _event|type|class_ will be removed from the current
      trace session.
  
* wall: shows the given text to all servers<br/>
   usage: **wall** _[text]_
  
  This will broadcast the given message to all the servers lited in
  _"serverlist"_ that have **rcon-pw** defined.  You must have **rcon**
  enabled and all servers should be running **GrabBag** with **wall**
  support.
  
* warp: Teleport to warp.<br/>
  usage: **warp**  _[player]_ _[warpname]_
  Teleports to _warpname_.  If no _warpname_ is given, it will list the
  warps available.
  
  Permissions are created with the form: **gb.warp.** _warpname_.
* whois: Gives detailed information on players<br/>
  usage: **whois** _&lt;player&gt;_
* xyz: shows the players position and bearing<br/>
   usage: **xyz** _[player]_

<!-- end-include -->

<!-- snippet: cmdnotes  -->

Commands scheduled by **at** and **after** will only run as
long as the server is running.  These scheduled commands will *not*
survive server reloads or reboots.  If you want persistent commands,
it is recommended that you use a plugin like
[TimeCommander](http://forums.pocketmine.net/plugins/timecommander.768/).

<!-- end-include -->

### Module reference

<!-- php:$h=4; -->
<!-- template: gd2/mods.md -->
#### blood-particles

Display particles when a player gets hit

#### broadcast-ft

Broadcast player's using FastTransfer

This listener module will broadcast when a player uses FastTransfer

#### broadcast-tp

Broadcast player teleports

This listener module will broadcast when a player teleports to
another location.  It also generates some smoke and plays a sound.


#### cmd-selector

Implements "@" command prefixes

Please refer to the CommandSelector section

#### custom-death

Customize what happens when a player dies

Currently this module only supports the _KeepInv_ feature.
This feature lets you select what happens with a player's inventory
when they get killed.

- default : This is the PocketMine-MP default, which the player
  loses their inventory and it is drop as pickable items.
- keep : The player gets to keep their inventory and nothing gets
  dropped.
- nodrops : The player loses their inventory but no items are dropped.
  This is useful to reduce the amount of Item Entities which in heavy
  used servers may cause lag.
- perms: Player permissions are checked on what to do.  Players must
  have one permission between these:
  - gb.cdeath.default
  - gb.cdeath.keep
  - gb.cdeath.nodrops


#### join-mgr

Customize players when they joins the server

This module does the following:

- Broadcast a message when an op joins
- Show the server's motd on connect.
- Keeps slots reserved for Ops (or VIPs)
- Players can start with equipment
- Always Spawn functionality

#### mega-slots

Merges the slot count of multiple servers

This module requres either the **motd-task** or **query-task**
to be enabled.

#### motd-task

Background task to ping configured remote servers

This module will ping servers in the server list to retrieve
motd/cache

#### query-task

Background task to query configured remote servers

This module will query servers in the server list to retrieve
query cacheable data.

#### repeater

Uses **!!** to repeat command with changes

If you want to repeat a previous command enter **!!** *without* any "/"
in front.  This works for commands and chat messages.

You can optionally append additional text to **!!** to do certain
things:

* **!!** number
  - Will let you paginate output.  For example, entering:
    - /mw ls
    - !!2
    - !!3
  - This will start showing the output of **/mw ls** and consecutive pages.
* **!!** /
  - if you forgot the "/" in front, this command will add it.  Example:
    - help
    - !!/
* **!!** _text_
  - Will append _text_ to the previous command.  For example:
    - /gamemode
    - !! survival john
  - This will show the usage of survival, the next line will change the
    gamemode of john to survival.
* **!!** str1 str2
  - Will repeat the previous command replacing `str1` with `str2`
    Example:
    - /give player drt
    - !!drt dirt
  - This will change **drt** into **dirt**.
* **!!^** _text_
  - Will insert _text_ at the beginning of the command.



<!-- end-include -->

<!-- template: test.md -->
<!-- MISSING TEMPLATE: test.md ->

<!-- end-include -->

### Command Selectors
<!-- snippet: cmdselector  -->

This adds "@" prefixes for commands.
See
[Command Prefixes](http://minecraft.gamepedia.com/Commands#Target_selector_arguments)
for an explanation on prefixes.

This only implements the following prefixes:

- @a - all players
- @e - all entities (including players)
- @r - random player/entity

The following selectors are implemented:

- c: (only for @r),count
- m: game mode
- type: entity type, use Player for player.
- name: player's name
- w: world

<!-- end-include -->

### API

Since version 2.3 of GrabBag, an API is available.  For more information
see the
[API documentation.](http://alejandroliu.github.io/pocketmine-plugins/apidocs/index.html)

Most of GrabBag features are available through the GrabBag API or through
libcommon (which is included with GrabBag).  If there is a feature missing
from the API, please file a bug report.

### Configuration

Configuration is through the **config.yml** file.
The following sections are defined:

<!-- php:$h=4; -->
<!-- template: gd2/cfg.md -->
#### broadcast-tp

*  world: world broadcast setting. If true, will broadcast teleports accross worlds.
*  local: local broadcast setting. This will broadcast teleports that go beyond this number.

#### chat-scribe

*  privacy: RE => text : used to clean-up logs
*  notice: Show this text whenever a player logs-in

#### cmd-selector

*  max-commands: Limit the ammount of commands generated by @ prefixes

#### custom-death

*  inv: default, keep, nodrops, perms

#### features


This section you can enable/disable commands and listener modules.
You do this in order to avoid conflicts between different
PocketMine-MP plugins.  It has one line per feature:

   feature: true|false

If **true** the feature is enabled.  if **false** the feature is disabled.

#### freeze-thaw

*  hard-freeze: how hard to freeze players. If **true** no movement is allowed.  If **false**, turning is allowed but not walking/running/flying, etc.

#### join-mgr

*  adminjoin: broadcast whenever an op joins
*  servermotd: show the server's motd when joining
*  reserved: Number of reserved slots (0 to disable)
*  spawn-items: List of items to include when joining
*  spawn-armor: List of armor to include when joining
*  spawn: default, always, world, home, perms

#### motd-task

*  ticks: how often tasks are fired...

#### query-task

*  ticks: how often tasks are fired...

#### serverlist

This section configures peer servers.  This can be used with
*rcon* and *query* commands.


<!-- end-include -->

### Permission Nodes

<!-- snippet: rtperms -->
* gb.cmd.afk (op): afk command
* gb.cmd.after (op): access command scheduler
* gb.cmd.alias (op): allow creating aliases
* gb.cmd.sudo (op): Run command as another user
* gb.cmd.blowup (op): Explode other players
* gb.cmd.burn (op): Burn other players
* gb.cmd.togglechat: lets players opt out from chat
* gb.cmd.togglechat.others (op): lets you toggle chat for others
* gb.cmd.togglechat.excempt (op): chat-off players will always receive chats from these players
* gb.cmd.togglechat.global (op): Can toggle chat for the server as a whole
* gb.cmd.clearchat: Clear your chat window
* gb.cmd.nick: Change display name
* gb.cmd.clearinv: clear player's inventory
* gb.cmd.clearinv.others (op): clear other's inventory
* gb.cmd.rminv: remove item from inventory
* gb.cmd.rminv.others (op): remove item from other's inventory
* gb.cmd.clearhotbar: clear player's hotbar
* gb.cmd.clearhotbar.others (op): clear other's hotbar
* gb.cmd.fixit (op): Fix player's held item
* gb.cmd.fixit.others (op): Fix other players held item
* gb.cmd.crash (op): crash dump management
* gb.cmd.echo: echo command
* gb.cmd.expand (op): expand command
* gb.cmd.rem: remark command
* gb.cmd.entities (op): entity management
* gb.cmd.fly (op): flight control
* gb.cmd.follow (op): lets you follow others
* gb.cmd.followme (op): let others follow you
* gb.cmd.freeze (op): freeze/thaw players
* gb.cmd.gma (op): Switch gamemode to Adventure
* gb.cmd.gms (op): Switch gamemode to Survival
* gb.cmd.gmc (op): Switch gamemode to Creative
* gb.cmd.gmspc (op): Switch gamemode to Spectator
* gb.cmd.heal (op): heal players
* gb.cmd.home: teleport to home location
* gb.cmd.sethome: set home command
* gb.cmd.sethome.new (op): set a new home
* gb.cmd.sethome.move: move existing home
* gb.cmd.delhome: Remove home
* gb.cmd.invisible (op): invisibility power
* gb.cmd.invisible.inmune (disabled): can see invisible players
* gb.cmd.iteminfo: get info on item held
* gb.cmd.iteminfo.other (op): item info of others
* gb.cmd.mute (op): mute/unmute players
* gb.cmd.onevent (op): access onevent command
* gb.cmd.opms: Send op only messages
* gb.cmd.rpt: Report issues
* gb.cmd.rpt.read (op): Read reported issues
* gb.cmd.ops: Display ops
* gb.cmd.permmgr (op): Manipulate Permissions
* gb.cmd.players: connected players
* gb.cmd.plenty (op): Give players plenty of stuff
* gb.cmd.pluginmgr (op): Run-time management of plugins
* gb.cmd.prefix: Prefix command
* gb.cmd.query: Query command
* gb.cmd.query.details: View details (info, plugins)
* gb.cmd.query.players: View players
* gb.cmd.query.players.showip: View players server IP
* gb.cmd.query.list: Query List sub command
* gb.cmd.rcon (op): Rcon client
* gb.cmd.reop: Reop command
* gb.cmd.reop.others (op): ReOp others
* gb.cmd.regs (op): Manage player registrations
* gb.module.cmdsel: use command selectors
* gb.cmd.setarmor (op): Configure armor
* gb.cmd.setarmor.others (op): Configure other's armor
* gb.cmd.shield (op): Allow players to become invulnverable
* gb.cmd.seearmor (op): View armor
* gb.cmd.seeinv (op): View inventory
* gb.cmd.skin (op): Manage skins
* gb.cmd.skin.other (op): Manage other's skins
* gb.cmd.slay (op): Allow slaying players
* gb.cmd.spawn: Teleport to spawn
* gb.cmd.spectator (op): Turn players into spectators
* gb.cmd.log (op): Allow players to enable logging
* gb.cmd.spy (op): Allow players to enable spying
* gb.spy.privacy (disabled): Players with this permission do not have logging/spying
* gb.cmd.servicemode (op): service mode command
* gb.servicemode.allow (op): login when in service mode
* gb.cmd.summon (op): summon|dismmiss command
* gb.cmd.throw (op): Troll players
* gb.cmd.back: Allow players to return to place of death
* gb.cmd.tpask (op): Submit a tp request
* gb.cmd.tpahere: Submit a tp-here request
* gb.cmd.tpaccept: Accept tpask|tpahere
* gb.cmd.tpaccept.tpask: Accept tpask
* gb.cmd.tpaccept.tpahere (op): Accept tpask
* gb.cmd.tpdecline: Decline tpask|tpahere
* gb.cmd.pushpoptp (op): position stack
* gb.cmd.top (op): top commnad
* gb.cmd.top.others (op): top others commnad
* gb.cmd.tracer (op): access event tracing
* gb.cmd.wall (op): broadcast command
* gb.cmd.warp: teleport to warp location
* gb.cmd.warp.other (disabled): warp others
* gb.cmd.setwarp (op): set home command
* gb.cmd.setwarp.new (op): set a new warp
* gb.cmd.setwarp.move: move existing warp
* gb.cmd.delwarp (op): Remove warp
* gb.cmd.whois (op): view players details
* gb.cmd.whois.showip (op): view players IP address
* gb.cmd.xyz: xyz command
* gb.cmd.xyz.others (op): xyz command on others
* gb.cdeath: players with this permission benefit from keepiinv
* gb.cdeath.default (disabled): Player dies according to PocketMine defaults
* gb.cdeath.keep (disabled): Player keeps inventory on death
* gb.cdeath.nodrops (disabled): Player does not drop items on death
* gb.join.reserved (op): players with this permission can use reserved slots
* gb.join.giveitems: receive items on join
* gb.join.givearmor: receive armor on join
* gb.join.spawn.default (disabled): Players with this permission join according to PocketMine defaults
* gb.join.spawn.always (disabled): Players with this permission will always spawn on the default world on join
* gb.join.spawn.world (disabled): Players with this permission will spawn in the last world on join
* gb.join.spawn.home (disabled): Players with this permission will join in their Home location
* gb.module.repeater: use !! to repeat commands
* gb.cmd.servers (op): servers command
* gb.cmd.servers.read (op): view server configuration
* gb.cmd.servers.read.viewip (op): view server IP address
* gb.cmd.servers.read.viewrcon (op): view rcon secrets
* gb.cmd.servers.write (op): change server configuration
* gb.cmd.ftserver (op): Allow user to use Fast Transfer
* gb.cmd.get (op): get blocks
* gb.cmd.near: nearby players
* gb.cmd.pmscript (op): access rc (pmscript) command
* gb.cmd.timings (op): view timings report
<!-- end-include -->

<!-- template: gd2/mctxt.md -->

## Translations

This plugin will honour the server language configuration.  The
languages currently available are:

* English
* Spanish


You can provide your own message file by creating a file called
**messages.ini** in the plugin config directory.
Check [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/GrabBag/resources/messages/)
for sample files.
Alternatively, if you have
[GrabBag](http://forums.pocketmine.net/plugins/grabbag.1060/) v2.3
installed, you can create an empty **messages.ini** using the command:

     pm dumpmsgs GrabBag [lang]

<!-- end-include -->

## Additional Libraries

The following third party libraries are included:

* [xPaw's MinecraftQuery](http://xpaw.me GitHub: https://github.com/xPaw/PHP-Minecraft-Query)

## WIP and issues

* Query:
  * Queries are done in the main thread.
  * Queries to the same server do not work.
* Deprecated modules:
  - CmdSpectator
* MCPE-0.12 has the following issues:
  - teleports are a bit glitchy
  - Heal command does not work properly
  - FastTransfer sessions do not free connections
  - Inventory gets out of sync
  - skins do not get loaded on the fly

# Changes

* 2.5.0: 2.0.0 API update
* 2.4.1: Minor update
  * Added FastTransfer workaround (libcommon)
  * /rc scripts, the first command line argument is {1} (instead of {0}).
* 2.4.0: libcommon bundle
  * Query/Ping Daemon functionality
  * Server configuration can have alternative IPs
  * Warps, Tp Request and Homes (Requested by @rvachvg, @rock2rap)
  * Added support for PNG skin formats
  * New Commands:
    * teleport: warps, top, back
    * informational: near, iteminfo, xyz
    * inventory: fixit, plenty
    * player mgmt: afk
    * Developer tools: echo, rem, expand, onevent, pmscript/rc, trace
    * Server management: wall, command logging and spying
  * JoinMgr: reserved slots, initial armor and items, always spawn
  * New modules: custom-death and merge-slots
  * CmdSpectator is now deprecated
  * libcommon is embedded and usable.  Replaces a separate libcommon library.
  * Bug Fixes and updates
    - Query works with query daemon (when available)
    - SetArmor was revamped, syntax changed.
    - Skinner implementation is part of libcommon.  It now has experimental
      support for PNG skins.  (Requires PHP with GD enabled)
    - etc...
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

<!-- end-include -->

