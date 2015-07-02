<img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/icon-toy-box.png" style="width:64px;height:64px" width="64" height="64"/>

# ToyBox

* Summary: A box full of fun toys and tools
* Dependency Plugins: N/A
* PocketMine-MP version: 1.4 (API:1.10.0), 1.5 (API:1.12.0)
* DependencyPlugins: -
* OptionalPlugins: N/A
* Categories: Fun
* Plugin Access: Blocks, Commands
* WebSite: https://github.com/alejandroliu/bad-plugins/tree/master/ToyBox

## Overview

<!-- php: $v_forum_thread = "http://forums.pocketmine.net/plugins/toybox.1135/"; -->
<!-- template: prologue.md -->

**DO NOT POST QUESTION/BUG-REPORTS/REQUESTS IN THE REVIEWS**

It is difficult to carry a conversation in the reviews.  If you
have a question/bug-report/request please use the
[Thread](http://forums.pocketmine.net/plugins/toybox.1135/) for
that.  You are more likely to get a response and help that way.

**NOTE:**

This documentation was last updated for version **1.1.2**.

Please go to
[github](https://github.com/alejandroliu/bad-plugins/tree/master/ToyBox)
for the most up-to-date documentation.

You can also download this plugin from this [page](https://github.com/alejandroliu/pocketmine-plugins/releases/tag/ToyBox-1.1.2).

<!-- template-end -->

Provide additional items with special functionality to PocketMine.

* TreeCapitator - axe that destroys trees quickly
* CompassTP - Teleporting compass
* Trampoline - Jump and down blocks
* CloakClock - Clock that gives Invisibility
* PowerTool - pickax that destroys blocks instantly
* Floating Torch - Floating torch that follows you around
* Magic Carpet - Fly with a carpet made of glass

## Configuration

	---

	# Enable or disable specifif toys
	modules:
	  treecapitator: true
	  compasstp: true
	  trampoline: true
	  powertool: true
	  cloakclock: true
	  floating-torch: true
	  magic-carpet: true

	# Configure torch data
	floating-torch:
	  item: TORCH
	  block: TORCH

	# Configure compass items
	compasstp:
	  item: COMPASS

	# Configure cloaking item
	cloakclock:
	  item: CLOCK

	# Configure power tools
	powertool:
	  ItemIDs:
	  - IRON_PICKAXE
	  - WOODEN_PICKAXE
	  - STONE_PICKAXE
	  - DIAMOND_PICKAXE
	  - GOLD_PICKAXE
	  need-item: true
	  item-wear: 1
	  creative: true

	# Configure TeeCapitator
	treecapitator:
	  ItemIDs:
	  - IRON_AXE
	  - WOODEN_AXE
	  - STONE_AXE
	  - DIAMOND_AXE
	  - GOLD_AXE
	  need-item: true
	  break-leaves: true
	  item-wear: 1
	  broadcast-use: true
	  creative: true

	# Configure trampoline blocks
	trampoline:
	  blocks:
	  - SPONGE
	magic-carpet:
	  block: GLASS
	...

### Permission Nodes

* toybox.treecapitator : Use treecapitator
  (Defaults to Op)
* toybox.magiccarpet : MagicCarpet command
  (Defaults to Op)
* toybox.compasstp : Teleport with compass
  (Defaults to Op)
* toybox.powertool : Allow the use of powertool
  (Defaults to Op)
* toybox.cloakclock.use : Can use cloakclock
  (Defaults to Op)
* toybox.cloakclock.inmune : Can see players using cloakclock
  (Defaults to Op)
* toybox.torch : Can use floating torch
  (Defaults to Op)


## Changes

* 1.1.2: Torch
  * Forgot to fix the torch
* 1.1.1: Maintenance release
  * Fixed magic carpet for 1.5
  * Translations: Spanish, English
* 1.1.0 : Next release
  * Added Floating Torch
  * Added magic carpet
  * Configuration is more readable
  * Removed CallbackTask deprecation warnings
* 1.0.0 : First submission

## Copyright

    ToyBox
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

