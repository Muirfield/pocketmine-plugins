<img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/helper.alt-icon.png" style="width:64px;height:64px" width="64" height="64"/>

SimpleAuthHelper
================

* Summary: Simplifies the SimpleAuth login process
* Dependency Plugins: n/a
* PocketMine-MP version: 1.4 - API 1.10.0
* DependencyPlugins: SimpleAuth
* OptionalPlugins: -
* Categories: General
* Plugin Access: Commands
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/SimpleAuthHelper)

Overview
--------

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

Documentation
-------------

As a bonus, it can start a player with initial inventory upon
registration.  This is configured through the `nest-egg` setting.

### Commands

* *chpwd* _<old-pwd>_  
  Used by players to change their passwords.
* *resetpwd* _<player>_  
  Used by ops to reset a players password.  This actually unregisters
  the password.

### Configuration

	---
	messages:
	  re-enter pwd: 'Please re-enter password to confirm:'
	  passwords dont match: |-
	    Passwords do not match.
	    Please try again!
	    Enter a new password:
	  register ok: You have been registered!
	  no spaces: |-
	    Password should not contain spaces
	    or tabs
	  not name: Password should not be your name
	  too many login: You have attempted to login too many times.
	  login timeout: Login timer expired!
	nest-egg:
	- "272:0:1"
	- "17:0:16"
	- "364:0:5"
	- "266:0:10"
	max-attempts: 5
	login-timeout: 60
	auto-ban: false
	...

* The section `messages` can be used to configure displayed texts.
* `nest-egg` section contains list of items that will be given to the
player upon registration.
* `max-attempts` counts the number of tries to login.
* `login-timeout` will kick the player out if not authenticated in
  that number of seconds.
* `auto-ban`: If set to true it will automatically ban the IP of a
  player that does too many login attempts.

Changes
-------

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

Copyright
---------

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
