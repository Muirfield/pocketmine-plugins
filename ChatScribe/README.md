<img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/ChatScribe-icon.png" style="width:64px;height:64px" width="64" height="64"/>

# ChatScribe

* Summary: Logs chat and commands to file
* Dependency Plugins: n/a
* PocketMine-MP version: 1.5 - API 1.12.0
* DependencyPlugins: -
* OptionalPlugins: -
* Categories: Admin
* Plugin Access: Commands, Data Saving
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/ChatScribe)

## Overview

**DO NOT POST QUESTION/BUG-REPORTS/REQUESTS IN THE REVIEWS**

It is difficult to carry a conversation in the reviews.  If you have a
question/bug-report/request please use the
[Thread](http://forums.pocketmine.net/threads/chatscribe.8922/) for
that.  You are more likely to get a response and help that way.

Please go to
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/ChatScribe)
for the most up-to-date documentation.

Let's you log all commands and chat's to files.

**To ensure user's privacy, there is a set of regular expressions that
will remove passwords before logging the line.  You can add additional
regular expressions if needed.**

Usage:

* /log [on|off]
  * with no arguments shows logging status
  * on : enables logging
  * off : disables logging
* /spy [start|stop|ls] [player]
  * logs what a player is doing in-game.  Useful to help noobs.

Users with the permission **chatscribe.privacy** do not have their
commands logged.

The following command lines are modified before logging so passwords
are **NEVER** stored:

* SimpleAuth related:
  * /login
  * /register
  * /unregister
* SimpleAuthHelper:
  * /chpwd

### Configuration

Configuration is throug the `config.yml` file.
The following sections are defined:

#### main

*  log: Either server or file
*  dest: output destination. If log is file, this is a filename, otherwise emergency|alert|critical|error|warning|notice|info|debug
*  default: If true, will start logging by default
*  spy: Allow logging in-game
*  privacy: regular expressions and replacements used for ensuring privacy
*  warning: Text to show warning that logging is available


### Permission Nodes

* chatscribe.cmd : Enable logging
  (Defaults to Op)
* chatscribe.privacy : No logging
  _(Defaults to disabled)_
* chatscribe.spy : Allow to spy on users in-game
  _(Defaults to disabled)_


## Changes

* 1.2.0: in-game logging and fixes
  * Added in-game logging (remote helpdesk support)
  * Added a warning that logging is available
* 1.0.1:
  * Fixed leak
  * Hard-coded some rules to avoid logging SimpleAuth passwords
  * Removed the early/later listener option
* 1.0.0: First release

## Copyright

    ChatScribe
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

