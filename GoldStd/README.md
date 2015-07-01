<img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/GoldStd2-icon.png" style="width:64px;height:64px" width="64" height="64"/>

# GoldStd

* Summary: Gold based economy plugin
* Dependency Plugins: N/A
* PocketMine-MP version: 1.5 (API:1.12.0)
* DependencyPlugins: -
* OptionalPlugins: N/A
* Categories: Economy
* Plugin Access: Commands, Entities, Items
* WebSite: https://github.com/alejandroliu/pocketmine-plugins/tree/master/GoldStd

## Overview

<!-- php: $v_forum_thread = "http://forums.pocketmine.net/threads/goldstd.8071/"; -->
<!-- template: prologue.md -->

**DO NOT POST QUESTION/BUG-REPORTS/REQUESTS IN THE REVIEWS**

It is difficult to carry a conversation in the reviews.  If you
have a question/bug-report/request please use the
[Thread](http://forums.pocketmine.net/threads/goldstd.8071/) for
that.  You are more likely to get a response and help that way.

**NOTE:**

This documentation was last updated for version **1.2.0**.

Please go to
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/GoldStd)
for the most up-to-date documentation.

You can also download this plugin from this [page](https://github.com/alejandroliu/pocketmine-plugins/releases/tag/GoldStd-1.2.0).

<!-- template-end -->

Implements an economy plugin based on Gold Ingots (by default) as the
currency.

Basic Usage:

* pay $$
* balance
* shopkeep spawn [player|location] [shop]

To pay people you tap on them while holding a gold ingot.

## Documentation

GoldStd implements an economy plugin based on Gold Ingots (by default)
as the currency.  This allows to add game mechanics to the game
without artificial commands or other artificial constructs.

You can then pay people without using the chat console.  Also, you may
lose all your money if you get killed.  Players can stash their gold
on Chests, but they would need to guard them (just like in real life),
etc.  You can see how much money you have directly in the inventory
window, etc.

### Commands

The chat console commands are there for convenience but are not needed
for regular gameplay:

* pay $$  
  By default when you tap on another player, only 1 gold ingot get
  transferred.  This command can be used to facilitate larger
  transactions.  If you use this command the next tap will transfer
  the desired amount in one go.
* balance  
  If you are rich enough, your money will be in multiple stacks.  This
  commands will add the stacks for you.
* shopkeep  
  This command is used to manage shop keepers.  Sub-commands:
  - /shopkeep spawn [player|location] [shop]
    - player - Shop keepr will be spawn where this player is located.
    - location: x,y,z or x,y,z,yaw,pitch or x,y,z,yaw,pitch,world
    - shop - shop definition from configuration file.

### Signs

GoldStd supports three types of signs.

1. Shop signs
2. Gambling signs
3. Trading signs

#### Shop Signs

Place a sign with the following text:

    [CODE]
    [SHOP]
    Item_name [x10]
    price
    <anything>
    [/CODE]

Players will be able to buy the specified items at the set price.
Defaults to 1, but this can be changed with the x?? modifier.
Examples from RuinPvP:

* [SHOP]
  * Wooden Sword
  * 200p
* [SHOP]
  * Golden Sword
  * 250p
* [SHOP]
  * Stone Sword
  * 500p
* [SHOP]
  * Iron Sword
  * 700p
* [SHOP]
  * Diamond Sword
  * 1000p
* [SHOP]
  * Apples x10
  * 200p

#### Gambling Signs

Tap a sign and you place a bet.  Place a sign with the following text:

    [CODE]
    [CASINO]
    <odds>:<payout>
    <price>
    [/CODE]

The odds is the number of chances, so the higher the number the less
chance of payout.  Payout is how much you get pay if you win.  Price
is how much each bet costs.
Examples:

* [CASINO]
  * 5:300
  * 100p

#### Trading Sings

Tap a sign and you can trade items.
Place a sign with the following text:

    [CODE]
    [TRADE]
    <item-you-get>
    <item-you-give>
    [/CODE]

Examples:

* [TRADE]
  * Diamond Sword
  * Wood x5
* [TRADE]
  * Spawn Egg:32
  * Emerald x2
  * Zombie Spawn egg

#### Potions Shop

Tap a sign and you get a potions effect.
Place a sign with the following text:

    [CODE]
    [POTIONS]
    <effect[:duration:amplifier]>
    <price>
    [/CODE]

Duration is a value in seconds.

Effects:

* [POTIONS]
  * 1:120:1
  * 10p
  * Speed
* [POTIONS]
  * STREGTH
  * 1p
* [POTIONS]
  * 8::2
  * 2p
  * Jump

### API

* API
  - getMoney
  - setMoney
  - grantMoney

### Configuration

Configuration is through the `config.yml` file.
The following sections are defined:

#### defaults


Default values for paying players by tapping
*  payment: default payment when tapping on a player
*  timeout: how long a transaction may last

#### main

*  settings: features
	*  currency: Item to use for currency false or zero disables currency exchange.
	*  signs: set to true to enable shops|casino signs
*  trade-goods: List of tradeable goods
*  signs: Text used to identify GoldStd signs

#### shop-keepers

*  enable: enable/disable shopkeep functionality
*  range: How far away to engage players in chat
*  ticks: How often to check player positions
*  freq: How often to  spam players (in seconds)


### Permission Nodes

* goldstd.cmd.pay : Access to pay command
* goldstd.cmd.balance : Show your current balance
* goldstd.cmd.shopkeep : ShopKeepr management
  (Defaults to Op)
* goldstd.signs.use : Allow access to signs
* goldstd.signs.use.casino : Allow access to casino signs
* goldstd.signs.use.shop : Allow access to shopping signs
* goldstd.signs.use.trade : Allow access to trading signs
* goldstd.signs.place : Allow placing signs
  (Defaults to Op)
* goldstd.signs.place.casino : Allow placing casino signs
  (Defaults to Op)
* goldstd.signs.place.shop : Allow placing shopping signs
  (Defaults to Op)
* goldstd.signs.place.trade : Allow placing trading signs
  (Defaults to Op)


## Translations

This plugin will honour the server language configuration.  The
languages currently available are:

* English
* Spanish

You can provide your own message file by creating a file called
`messages.ini` in the plugin config directory.  Check
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/GoldStd)
for sample files.

The contents of these "ini" files are key-value pairs:

	"Base text"="Translated Text"

## FAQ

* Q: What do I do if the item name does not fit in the sign?
* A: For those case you should use the Item ID and if you need more descrption add it to line 4, or on a different sign.
* Q: Where do I find a list of the proper item names?
* A: This uses PocketMine definitions (like the /give command).  You can find the list here:
  * [PocketMine Source](https://github.com/PocketMine/PocketMine-MP/blob/master/src/pocketmine/item/Item.php#L39)
  * Note that item names are case insensitive.  You can use the names or the ids.
* Q: Where do I find a list of the proper effect names?
  * [PocketMine Source](https://github.com/PocketMine/PocketMine-MP/blob/master/src/pocketmine/entity/Effect.php#L32)
  * You can use these names or the ids.
* Q: How do I set a staring amount of money for players?
* A: Use SpawnMgr or ItemSpawn to define an initial inventory which should include gold ingots.
* Q: How can I use a different money plugin?
* A: Set the currency to false in config.yml.  Then GoldStd will search for an
  alternate money plugin.

# Changes

* ????:
  * Weapons' are detected using isSword, isAxe and isPickaxe.

* 1.2.0:
  * MoneyAPI fixes (Thanks @vertx)
  * Effects Shop
  * Can specify location in spawn command
  * Fixed spamming with multiple shops.
* 1.1.2 :
  * @Achak request
    * Added goods trading
  * Added casino, shop and trading signs
  * Configuration uses strings instead of codes
  * Compatible with other economy plugins
  * ShopKeep functionality
* 1.0.0 : First submission

# Copyright

    GoldStd
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

