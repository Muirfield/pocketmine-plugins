<img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/EnderChest.png" style="width:64px;height:64px" width="64" height="64"/>

# NetherChests

* Summary: An Ender Chest type plugin
* Dependency Plugins: N/A
* PocketMine-MP version: 1.5 (API:1.12.0)
* OptionalPlugins: -
* Categories: mechanics
* Plugin Access: Databases, Tile Entities
* WebSite: https://github.com/alejandroliu/pocketmine-plugins/tree/master/NetherChests

## Overview

<!-- php: $v_forum_thread = "http://forums.pocketmine.net/threads/netherchests.9269/"; -->
<!-- template: prologue.md -->

**DO NOT POST QUESTION/BUG-REPORTS/REQUESTS IN THE REVIEWS**

It is difficult to carry a conversation in the reviews.  If you
have a question/bug-report/request please use the
[Thread](http://forums.pocketmine.net/threads/netherchests.9269/) for
that.  You are more likely to get a response and help that way.

_NOTE:_

This documentation was last updated for version **1.1.1**.

Please go to
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/NetherChests)
for the most up-to-date documentation.

You can also download this plugin from this [page](https://github.com/alejandroliu/pocketmine-plugins/releases/tag/NetherChests-1.1.1).

<!-- template-end -->

An Ender Chest implementation.

Place a Chest on top of a NettherRack, will turn that chest into an NetherChest.

### Configuration

Configuration is through the `config.yml` file.
The following sections are defined:

#### config.yml

*  settings: Configuration settings
	*  global: If true all worlds share the same NetherChest
	*  particles: Decorate NetherChests...
	*  p-ticks: Particle ticks
	*  base-block: Block to use for the base
*  backend: Use YamlMgr or MySqlMgr
*  MySql: MySQL settings. Only used if backend is MySqlMgr to configure MySql settings


## Permission nodes

# FAQ

* Q: What happens when more than one player use the NetherChest?
* A: when a player opens a NetherChest they see their own inventory,
  not somebody elses.  However, only one player can use a NetherChest
  at a time.
* Q: It doesn't work with iProtector!
* A: I don't use iProtector.  This plugin only listens to
  _InventoryOpen_ and _InventoryClose_ (it also listens to
  _BlockPlace_ and _PlayerQuit_, but that has nothing to do with
  opening chests).  iProtector listens and **Cancels** PlayerInteract
  events.  These are fired **before** Inventory events.  So, if you
  are not able to get it to work with _iProtector_, your configuration
  is **wrong**.

# Changes

* 1.1.1: Bug fix
  - Fixed item duplication cheat/bug (Reported by @predawnia)
* 1.1.0 : flexibility
  - more configuration, NetherChests can be global now
  - MySQL support
  - Translation: English, Spanish, Dutch
* 1.0.0 : First submission

# Copyright

    NetherChests
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

