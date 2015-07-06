<img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/helper.alt-icon.png" style="width:64px;height:64px" width="64" height="64"/>

# SimpleAuthHelper


* Summary: Simplifies the SimpleAuth login process
* Dependency Plugins: n/a
* PocketMine-MP version: 1.4 - API 1.10.0
* DependencyPlugins: SimpleAuth
* OptionalPlugins: -
* Categories: General
* Plugin Access: Commands
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/SimpleAuthHelper)

## Overview


Very simple plugin that simplifies the login process... Instead of
asking for commands, users simply chat away...

### Register process

Player connects for the first time.  They are prompted to enter a
*NEW* password.  They enter their password directly, without having to
enter */register*.

They are asked for the password again to confirm.  They re-enter their
password (again without */register*).

### Login process

Player connects again.  They are prompted to enter their login
password.  They type their login password directly (without
*/login*).  And they are in.

## Documentation

As a bonus, it can start a player with initial inventory upon
registration.  This is configured through the **nest-egg** setting.

### Commands

* *chpwd* _<old-pwd>_
  * Used by players to change their passwords.
* *resetpwd* _<player>_
  * Used by ops to reset a players password.  This actually unregisters
    the password.

### Configuration

	```YAML
	[CODE]
	max-attempts: 5
	login-timeout: 60
	leet-mode: true
	chat-protect: true
	[/CODE]

* **max-attempts** counts the number of tries to login.
* **login-timeout** will kick the player out if not authenticated in
  that number of seconds.
* **leet-mode**: If enabled, will allow user to still use **/login** when
  authenticating.
* **chat-protect**: Monitors chat lines and if it notices a user
  entering their password it will stop that.

### Permissions

* simpleauthhelper.command.chpwd: User can change password
* simpleauthhelper.command.resetpwd: Ops can reset a user's password

# Changes

* 1.3.0: Modularization
  - uses now a common translation library
  - some minor tweaks
  - Removed little use feature: nest-egg
  - leet-mode also works for /register.
  - Removed auto-ban.  It is now done in SimpleAuth.
  - Added support for hiding unauthenticated players (Suggested by @CaptainKenji17)
* 1.2.3: Security improvements
  - prevent user from chatting away their password
  - add option so that players can also use "/login" to login.
* 1.2.2: Auto-Ban
  - Too many login attempts will cause the player to be banned.
* 1.2.1: CallbackTask deprecation
  * Removed CallbackTask deprecation warnings
* 1.2.0: max-logins
  * Suggestion from MCPEPIG
    - kick user out after `max-attempts`.
    - Added a chpwd command.
  * Kick user out if not authenticated after `timeout` seconds.
  * Added resetpwd command for ops
* 1.1.0: Small update
  * Added `nest-egg`
  * Messages can be configured.
* 1.0.0: First release

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
