<img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/helper.alt-icon.png" style="width:64px;height:64px" width="64" height="64"/>

<!-- meta:Categories = AdminTools -->
<!-- meta:PluginAccess = Commands, Other Plugins, Manages Permissions -->
<!-- template: gd2/header.md -->

# SimpleAuthHelper

- Summary: Simplifies the way people authenticate to servers
- PocketMine-MP version: 1.5 (API:1.12.0)
- DependencyPlugins: SimpleAuth
- OptionalPlugins: 
- Categories: AdminTools 
- Plugin Access: Commands, Other Plugins, Manages Permissions 
- WebSite: https://github.com/alejandroliu/pocketmine-plugins/tree/master/SimpleAuthHelper

<!-- end-include -->

## Overview

<!-- php: $v_forum_thread = "http://forums.pocketmine.net/threads/simpleauthhelper.8074/"; -->
<!-- template: prologue.md -->

**DO NOT POST QUESTION/BUG-REPORTS/REQUESTS IN THE REVIEWS**

It is difficult to carry a conversation in the reviews.  If you
have a question/bug-report/request please use the
[Thread](http://forums.pocketmine.net/threads/simpleauthhelper.8074/) for
that.  You are more likely to get a response and help that way.

_NOTE:_

This documentation was last updated for version **2.0.3**.

Please go to
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/SimpleAuthHelper)
for the most up-to-date documentation.

You can also download this plugin from this [page](https://github.com/alejandroliu/pocketmine-plugins/releases/tag/SimpleAuthHelper-2.0.3).

<!-- end-include -->

A plugin that simplifies the login process. Instead of
asking for commands, users simply chat away.

I also provides for a number of tweaks that can improve the usability of
[SimpleAuth](https://forums.pocketmine.net/plugins/simpleauth.4/).

#### Register process

Player connects for the first time.  They are prompted to enter a
_NEW_ password.  They enter their password directly, without having to
enter **/register**.

They are asked for the password again to confirm.  They re-enter their
password (again without **/register**).

#### Login process

Player connects again.  They are prompted to enter their login
password.  They type their login password directly (without
**/login**).  And they are in.

## Documentation

### Commands

* *chpwd* _&lt;old-pwd&gt;_
  * Used by players to change their passwords.
* *resetpwd* _&lt;player&gt;_
  * Used by ops to reset a players password.  This actually unregisters
    the password.
* *preregister* _&lt;player&gt;_  _&lt;passwd&gt;_
  * Used by ops to pre-register players.
* *logout*
  * De-authenticates a player.

### Player pre-registration

It is possible to implement a web based pre-registration system with this
plugin.

1. *rcon* must be enabled on the PocketMine server.
2. web server must be able to send *rcon* commands to PocketMine.
3. Enable the *whitelist* functionality in PocketMine.
4. Install *SimpleAuth* and *SimpleAuthHelper*.
5. **Optionally** install *PurePerms* and disable
   `simpleauthhelper.command.chpwd` permission.  You probably want
   users to change passwords from the web site.
6. Whenever a user registers in web site, the web site script uses *rcon*
   to send the follwoing:
   - whitelist add _player_
   - preregister _player_ _passwd_
7. Whenever a user changes password in web site, we use *rcon* with:
   - resetpwd _player_
   - preregister _player_ _passwd_

### Database Monitor

This module is responsible for monitoring the SimpleAuth data provider
to make sure that it is up and running and disable logins if it is not
available.

It kicks off a background task that will poll the SimpleAuth data provider
by trying to retrieve the data from the "canary-account".  It is important
that you have configured and have working SimpleAuth provider the first
time you enable the database monitor.  This is because the "canary-account"
needs to be created (if it doesn't exist already).

On a regular interval, the SimpleAuth
data provider is checked.  If it is not running, all unauthenticated players
are kicked and any new joins are not allowed.

<!-- php:$h=3; -->
<!-- template: gd2/permissions.md -->

### Permission Nodes

* simpleauthhelper.command.chpwd: Allow users to change passwords
* simpleauthhelper.command.logout: Allow users to logout
* simpleauthhelper.command.resetpwd (op): Allow ops to reset other's passwords
* simpleauthhelper.command.prereg (op): Allow ops to pre-register users

<!-- end-include -->

### Configuration

Configuration is through the **config.yml** file.
<!-- php:$h=4; -->
<!-- template: gd2/cfg.md -->
#### main

*  max-attemps: kick player after this many login attempts.  NOTE: This conflicts with SimpleAuth's blockAfterFail setting
*  login-timeout: must authenticate within this number of seconds
*  leet-mode: lets players use also /login and /register
*  chat-protect: prevent player to display their password in chat
*  hide-unauth: EXPERIMENTAL, hide unauthenticated players
*  event-fixer: EXPERIMENTAL, cancels additional events for unauthenticated players
*  hack-login-perms: EXPERIMENTAL, overrides login permisions to make sure players can login
*  hack-register-perms: EXPERIMENTAL, overrides register permisions to make sure players can register
*  db-monitor: EXPERIMENTAL, enable database server monitoring
*  monitor-settings: Configure database monitor settings

#### monitor-settings

*  canary-account: account to query this account is tested to check database proper operations
*  check-interval: how to often to check database (seconds)


<!-- end-include -->

<!-- template: gd2/mctxt.md -->

## Translations

This plugin will honour the server language configuration.  The
languages currently available are:

* English
* German
* Spanish
* 中文


You can provide your own message file by creating a file called
**messages.ini** in the plugin config directory.
Check [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/SimpleAuthHelper/resources/messages/)
for sample files.
Alternatively, if you have
[GrabBag](http://forums.pocketmine.net/plugins/grabbag.1060/)
installed, you can create an empty **messages.ini** using the command:

     pm dumpmsgs SimpleAuthHelper [lang]

<!-- end-include -->

### Issues

* Event Fixer: Crafting canceling doesn't work

## Changes

* 2.0.3: Password disclosure work-around
  - Works around bugs in SimleAuth that makes users' passwords visible.
* 2.0.2: Added translation
  - Added a zho.ini (中文) message file. (Contributed by @edwinyoo44, closes #23)
  - Added a deu.ini (German) message file. (Contributed by @thebigsmileXD)
  - Documentation and library updates.
* 2.0.1: language defaults
  - make sure that languages default to English (reported by @minebuilder0110)
* 2.0.0: Major upgrade
  - uses now a common translation library
  - Removed little used feature: nest-egg
  - leet-mode also works for /register.
  - Removed auto-ban.  It is now done in SimpleAuth.
  - Added support for hiding unauthenticated players (Suggested by @CaptainKenji17)
  - Added pre-register and logout command
  - forces permissions to be set
  - Added a task to monitor database server status
  - Thanks @rvachvg for helping debug this.
* 1.2.3: Security improvements
  - prevent user from chatting away their password
  - add option so that players can also use "/login" to login.
* 1.2.2: Auto-Ban
  - Too many login attempts will cause the player to be banned.
* 1.2.1: CallbackTask deprecation
  * Removed CallbackTask deprecation warnings
* 1.2.0: max-logins
  * Suggestion from @MCPEPIG
    - kick user out after `max-attempts`.
    - Added a chpwd command.
  * Kick user out if not authenticated after `timeout` seconds.
  * Added resetpwd command for ops
* 1.1.0: Small update
  * Added `nest-egg`
  * Messages can be configured.
* 1.0.0: First release

<!-- php:$copyright="2015"; -->
<!-- template: gd2/gpl2.md -->
# Copyright

    SimpleAuthHelper
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

