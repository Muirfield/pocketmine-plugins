<img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/RSS-icon.png" style="width:64px;height:64px" width="64" height="64"/>

# LiveSigns

* Summary: Signs and floating text that change contents (e.g. twitter?)
* Dependency Plugins: N/A
* PocketMine-MP version: 1.5 (API:1.12.0)
* DependencyPlugins: -
* OptionalPlugins: N/A
* Categories: Informational
* Plugin Access: Tiles, Internet Services, Commands
* WebSite: https://github.com/alejandroliu/pocketmine-plugins/tree/master/LiveSigns

## Overview

<!-- php: $v_forum_thread = "http://forums.pocketmine.net/plugins/livesigns.1249/"; -->
<!-- template: prologue.md -->

**DO NOT POST QUESTION/BUG-REPORTS/REQUESTS IN THE REVIEWS**

It is difficult to carry a conversation in the reviews.  If you
have a question/bug-report/request please use the
[Thread](http://forums.pocketmine.net/plugins/livesigns.1249/) for
that.  You are more likely to get a response and help that way.

**NOTE:**

This documentation was last updated for version **1.1.0**.

Please go to
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/LiveSigns)
for the most up-to-date documentation.

You can also download this plugin from this [page](https://github.com/alejandroliu/pocketmine-plugins/releases/tag/LiveSigns-1.1.0).

<!-- template-end -->

LiveSigns is a plugin to display texts in signs or as floating text
from a number of sources and can change automatically as the sources
change.

Currently available sources:

* configuration file
* text file
* web url
* RSS feed
* Twitter feed
* php scripts
* MinecraftQuery

Sample configuration files are provided to get you started.  Basic
usage is that you create a LiveSign source, and assign it an id.  Then
in the game you place a sign with that id, and it will start
displaying it.

Sign Format:

* **[livesign]**
* _id_
* _line:step_
* _options_

The entry _line:step_ is optional and is used to have multi-sign
messages.

Also _options_ can be omitted, but can be one of the following:

* **raw** or **none** : Will **not** do any wrapping of long lines.
* **word** : Will wrap long lines at word boundaries.  _(this is the default)_
* **char** or **chr**:  Will wrap long lines (independant of word boundaries).

If omitted, long lines will be wrapped at word boundaries.

You can create floating text signs.  To do this use:

* /fs add [x,y,z[:world]|player] &lt;idtxt&gt;

Where:

* x,y,z - are position coordinates
* world - is the target world
* player - is a player where to spawn the text
* idtxt - is the configured LiveSign to span.

Examples:

* /fs add 128,-2,128 basic1
  - creates FloatingText for LiveSign basic1 2 blocks above ground
* /fs add 128,100,128:world basic2
  - creates FloatingText for LiveSign basic1 in world, at that position
* /fs add myname file
  - creates FloatingText at the position of player.

## Documentation

Use the **/livesign** command to access the plugin functionality.  The
following sub-commands are available:

* **cfg** _[id]_
   * show the configured sources
* **show** _[id]_
   * show the retrieved texts
* **set** _&lt;id&gt;_ _&lt;type&gt;_ _&lt;content&gt;_
   * create a new source with _id_.  See source types for the _type_.
* **rm** _&lt;id&gt;_
   * remove a livesign source
* **update** _&lt;id&gt;_
   * retrieve again the text for the specified source.
* **reload** _&lt;id&gt;_
   * reload signs configuration.
* **status**
   * show status of async task
* **announce** _&lt;id&gt;_
   * broadcast the livesign on the chat

The command **/floatsign** is used to manage floating text.  The
following sub-commands are available:

* **ls** _[world]_
  * Show the signs in the given _world_.
* **add** _[x,y,z[:world]|player]_ _&lt;idtxt&gt;_
  * Creates a sign
* **rm** _&lt;x,y,z[:world]|player|[world] idtxt&gt;_
  * Remove sign

### LiveSign sources

LiveSign sources can be created using the **/livesign** command or by
modifying **signs.yml**.  The following sources are possible:

* text
  * This is text embedded in the signs.yml file.  Can be
    a single line, or if multiple lines are needed, then
   you can provide them as a list.
* file
  * Points to a file in the plugin directory.
* url
  * points to a URL that will be fetched.
* rss
  * points to an URL to an RSS feed.  The content
    must contain the URL.  You can optionally provide
    additional settings to select an item from a feed or
    the atom to display
* twitter
  * points to a twitter feed.  You can optional provide
    a number which picks the update.
* php
  * points to a file in the plugin directory that will be executed as
    a php script.
* query
  * Get the output of a MinecraftQuery.  You should configure it with:
  * _server-name[,port[,message]]_
  * The _server-name_ is the host name to query.
  * The _port_ is optional, defaults to 19132.  Set to whatever is the port
    your server is running on.
  * _message_ is optional, will default to showing the MOTD, Current player
    count and max players.

Entries in **signs.yml** contain the following keys:

* sign-id:
  * type: type of source
  * content: either a single entry or array entities.
  * Optional settings:
  * no-vars: true, disables variable substitutions
  * max-age: seconds, overrides message cache settings


### Floating text

Floating text can be created using **/floatsign** or by editing
**floats.yml**.

This file contains entries as follows:

* pos: x:y:z
  * position where to place the text
* text: idtxt
  * id as defined in **signs.yml**.
* opts: _options_
  * Optional, can be omitted.  Contains comma separated options.  The
    following options are possible:
    * width=nn : Sets the word wrap width to _nn_.  Defaults to 75.
    * word : wrap at word boundaries
    * char : wrap per character

### Enabling Twitter feeds

First you need to create a twitter app.  To do that go to
[How to register a Twitter App](http://iag.me/socialmedia/how-to-create-a-twitter-app-in-8-easy-steps/)
and configure your twitter details in config.yml:

```
[CODE]
'oauth_access_token' => "YOUR_OAUTH_ACCESS_TOKEN",
'oauth_access_token_secret' => "YOUR_OAUTH_ACCESS_TOKEN_SECRET",
'consumer_key' => "YOUR_CONSUMER_KEY",
'consumer_secret' => "YOUR_CONSUMER_SECRET"
[/CODE]
```

### Multi sign's messages

Since the space in a sign is quite limited, sometimes is necessary to
span a message accross multiple signs.  This is accomplished using the
_line:step_ setting that is on the third line of a sign.  The way it
works is that the LiveSign text is split into lines.  The first number
in _line:step_ is the starting line number (the first line is zero).
Then, for the second line in the sign, we would add _step_ and pick
the corresponding line of the message.  So if you want to make a
message made of 2 signs accross, you would have:

|     |     |
|-----|-----|
| 0:2 | 1:2 |

Whereas if you wanted to have a message of 3 signs accross, 2 signs
up you would use:

|      |      |      |
|------|------|------|
| 0:3  | 1:3  | 2:3  |
| 12:3 | 13:3 | 14:3 |

### Variable substitutions

The following variables are available and can be substituted (this applies
to all sources except for **php**).

* {LiveSigns}
* {MOTD}
* {NL}
* {BLACK}
* {DARK_BLUE}
* {DARK_GREEN}
* {DARK_AQUA}
* {DARK_RED}
* {DARK_PURPLE}
* {GOLD}
* {GRAY}
* {DARK_GRAY}
* {BLUE}
* {GREEN}
* {AQUA}
* {RED}
* {LIGHT_PURPLE}
* {YELLOW}
* {WHITE}
* {OBFUSCATED}
* {BOLD}
* {STRIKETHROUGH}
* {UNDERLINE}
* {ITALIC}
* {RESET}
* {tps}
* {players}
* {maxplayers}

Special variables for Query messages:

* {HostName}
* {GameType}
* {GameName}
* {Version}
* {Map}
* {Players}
* {MaxPlayers}
* {HostIp}
* {HostPort}
* {RawPlugins}
* {Software}

### Additional Libraries

* RSS support: [lastRSS](http://lastrss.webdot.cz/)
* Twitter Support: [Twitter-API-PHP](http://github.com/j7mbo/twitter-api-php)
* Query Support: [xPaw's Minecraft Query](https://github.com/xPaw/PHP-Minecraft-Query)

### Configuration files

In addition to the standard **config.yml** file, the additional files
are read:

* **signs.yml**
  - this file contains definitions of sign sources.  And this may
    refer to additional files containing text or PHP code.
* **floats.yml**
  - this file contains definitions of floating texts.  For the actual
    floating text contents look in **signs.yml**.

**signs.yml** can refer to additional files or URLs to provide live
content.  Please look at the provided examples on how these need to be
set-up.  If you deleted your examples, you can always refer to them
back going back to [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/LiveSigns)

### Configuration

Configuration is through the `config.yml` file.
The following sections are defined:

#### main

*  settings: tunable parameters
	*  tile-updates: How often to update signs in game from cache (in-ticks)
	*  cache-signs: How long to cache sign data (seconds)
	*  expire-cache: How often to expire caches (ticks)
	*  path: file path for the file fetcher
	*  twitter: Used by the twitter feed fetcher
*  signs: trigger text


### Permission Nodes

* livesigns.cmd : Main livesign command
  (Defaults to Op)
* livesigns.cmd.addrm : Update livesign
  (Defaults to Op)
* livesigns.cmd.info : Show status of livesigns
* livesigns.cmd.update : Refresh livesigns
  (Defaults to Op)
* livesigns.cmd.broadcast : Broadcast livesigns in chat
  (Defaults to Op)
* floatsigns.cmd.ls : Show floating signs
* floatsigns.cmd.addrm : Show floating signs
  (Defaults to Op)


# Changes

* ???
  * moved Query's to AsyncTask
  * caching is more flexible
  * php scripts can now be cached (note that since it runs on an asynctask
    access to the PocketMine MP API is not possible)
* 1.1.0: new features
  * Added query support
  * Added variable substitutions (for colors) (Requested by @iDirtPlayzMC)
  * We default to word wrap now.  Wrappings supports color codes.
* 1.0.1 : First update
  * Fixed FloatingTextParticle not updating text properly...
  * Added more info on commands
* 1.0.0 : First submission

# Copyright

    LiveSigns
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
