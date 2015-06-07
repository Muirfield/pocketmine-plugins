<img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/portal-icon.jpg" style="width:64px;height:64px" width="64" height="64"/>

# MagicTelePortal

* Summary: Easy to use Portal plugin
* Dependency Plugins: N/A
* PocketMine-MP version: 1.4 - API 1.10.0
* DependencyPlugins: -
* OptionalPlugins: FastTransfer
* Categories: Fun
* Plugin Access: Commands, Data Saving, World Editing
* WebSite: [github](https://github.com/alejandroliu/pocket-plugins/tree/master/MagicTelePortal)

Overview
--------

**DO NOT POST QUESTION/BUG-REPORTS/REQUESTS IN THE REVIEWS**

It is difficult to carry a conversation in the reviews.  If you have a
question/bug-report/request please use the
[Thread](http://forums.pocketmine.net/threads/magicteleportal.8053/) for
that.  You are more likely to get a response and help that way.

Please go to
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/MagicTelePortal)
for the most up-to-date documentation.

Simple plugin to make creation of portals easy.

Documentation
-------------

### Commands

You use one command:

    [CODE]
    /mtp [world|server:port] [x y z]
    [/CODE]

Examples:

* Portal to another world:
  * /mtp minigames
* Portal to another location in the same world:
  * /mtp 128 70 128
* Portal to an specific location in another world:
  * /mtp minigames 392 70 939
* FastTransfer portal
  * /mtp example.com:19132

### Configuration

```YAML
[CODE]
# How far can the portals be created
max-dist: 8
# Block-id of the border block (defaults to Nether Bricks)
border: 112
# Block-id of the center block (defaults to Still Water)
center: 9
# Block-id of the corners (defaults to Nether Brick Stairs)
corner: 114
[/CODE]
```

### Permission Nodes:

* mtp.cmd.mtp: Permission to create portals
* mtp.destroy: Permission to destroy portals

FAQ
---

* Q: How do I prevent people from breaking my portal?
* A: Use an anti-grief plugin or the `mtp.destroy` permission.

Changes
-------

* 1.3.0 : Bug-fix
  * Rewrote `manyworld` dependancy
  * Added a base to the portal (so water won't leak)
  * Translations: Spanish
  * Fixed so that when people Transfer, they do not enter in the
    location of the portal.
* 1.2.0 : Simple update
  * Renamed to MagicTelePortal.  
    **YOU MUST UPDATE CONFIG FILES**
  * Added `mtp.destroy` permission
* 1.1.0 : Next release
  * Support for FastTransfer
  * Some configuration options
* 1.0.0 : First submission

Copyright
---------

    MagicTelePortal
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
