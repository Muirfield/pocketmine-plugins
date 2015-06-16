<img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/GoldStd2-icon.png" style="width:64px;height:64px" width="64" height="64"/>

# GoldStd

* Summary: A different economy plugin
* Dependency Plugins: N/A
* PocketMine-MP version: 1.4 - API 1.10.0
* DependencyPlugins: -
* OptionalPlugins: N/A
* Categories: Economy
* Plugin Access: Commands, Entities, Items
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/GoldStd)

## Overview

**DO NOT POST QUESTION/BUG-REPORTS/REQUESTS IN THE REVIEWS**

It is difficult to carry a conversation in the reviews.  If you have a
question/bug-report/request please use the
[Thread](http://forums.pocketmine.net/threads/goldstd.8071/) for
that.  You are more likely to get a response and help that way.

Please go to
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/GoldStd)
for the most up-to-date documentation.

Implements an economy plugin based on Gold Ingots (by default) as the
currency.

Basic Usage:

* pay $$
* balance

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

### Signs

RunePvP supports three types of signs.

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

### API

* API
  - getMoney
  - setMoney
  - grantMoney

### Configuration

Configuration is throug the `config.yml` file.
The following sections are defined:

#### defaults


Default values for paying players by tapping
*  payment: default payment when tapping on a player
*  timeout: how long a transaction may last

#### main

*  settings: features
	*  currency: Item to use for currency
	*  signs: set to true to enable shops|casino signs
*  trade-goods: List of tradeable goods
*  signs: Text used to identify GoldStd signs


### Permission Nodes

* goldstd.cmd.pay : Access to pay command
* goldstd.cmd.balance : Show your current balance
* goldstd.signs.use : Allow access to signs
* goldstd.signs.use.casino : Allow access to casino signs
* goldstd.signs.use.shop : Allow access to shopping signs
* goldstd.signs.place : Allow placing signs
  (Defaults to Op)
* goldstd.signs.place.casino : Allow placing casino signs
  (Defaults to Op)
* goldstd.signs.place.shop : Allow placing shopping signs
  (Defaults to Op)


## Translations

This plugin will honour the server language configuration.  The
languages currently available are:

* English
* Spanish

You can provide your own message file by creating a file called
`messages.ini` in the plugin config directory.  Check
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/GoldStd/resources/messages/)
for sample files.

The contents of these "ini" files are key-value pairs:

	"Base text"="Translated Text"

# Changes

* 1.1.0 :
  * @Achak request
    * Added goods trading
  * Added casino, shop and trading signs
  * Configuration uses strings instead of codes
  * EXPERIMENTAL: Compatible with other economy plugins
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

