RunePvP
=======

* Summary: A funny PvP manager plugin
* Dependency Plugins: N/A
* PocketMine-MP version: 1.4 - API 1.10.0
* DependencyPlugins: -
* OptionalPlugins: SignShop,PocketMoney,MassiveEconomy,EconomyAPI
* Categories: Fun
* Plugin Access: Commands, Entity
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/RunePvP)

Overview
--------

A simple PvP manager inspired by RuinPvP.  Unlike RuinPvP this one
works with the current API and has a different gameplay more centered
around signs instead of entering commands.

It needs a economy plugin like for example, PocketMoney.

### Rules:

At first, every users gets 500 points.  This is configured in
PocketMoney (or whatever Economy plugin you use).

If players kills another player, the winner gets 100 points and the
loser loses 100 points.  Once players are under 100 points they will
not loose any points.  And winners only get 50 points for these kills.

Whenever players reach 10 kills, they get a level up and get a 1,000
points prize.

Players can then use the points they got to gamble or buy items using
`Signs` across the playing world.

Basic Usage:

* runepvp - Will show your RunePvP stats.
* runepvp stats [player] - Show the RunePvP stats for [player]
* runepvp top [online] - Show top players.

Documentation
-------------

This plugin supports PocketMoney and has experimental support for
MassiveEconomy and EconomyAPI.

### Commands

* runepvp - Will show your RunePvP stats.
* runepvp stats [player] - Show the RunePvP stats for [player]
* runepvp top [online] - Show top 5 players.

### Signs

RunePvP supports three types of signs.

1. Shop signs
2. Gambling signs
3. Informational signs

### Shop Signs

Place a sign with the following text:

    [SHOP]
    Item_name [x10]
    price
    <anything>

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

### Gambling Signs

Tap a sign and you place a bet.  Place a sign with the following text:

    [CASINO]
    <odds>:<payout>
    <price>

The odds is the number of chances, so the higher the number the less
chance of payout.  Payout is how much you get pay if you win.  Price
is how much each bet costs.
Examples:

* [CASINO]
  * 5:300
  * 100p

### Informational Signs

These signs show either rankings or the player's stats.  Just place a
sign with the first line of text with:

* `[STATS]` : Player stats.   Every player sees their own stats.
* `[RANKINGS]` : All time top 3.
* `[ONLINE RANKS]` : Ranking of only players on-line

If you tap on the rakings or online-rankings sign you get a list of
the top *five* players with level and kill numbers.

### Configuration

These can be configured from `config.yml`:

    points:
      kills: 100
      level: 1000
    signs:
      stats:
      - '[STATS]'
      rankings:
      - '[RANKINGS]'
      onlineranks:
      - '[ONLINE RANKS]'
      shop:
      - '[SHOP]'
      casino:
      - '[CASINO]'
    settings:
      dynamic-updates: 1

* points
  * kills : number of points awarded to a player for each kill.
  * level : number of points awarded to a player for leveling up.
* signs : customize the text of the signs.  Multiple text alternatives
  are possible and allowed.  For example:
    shop:
    - '[SHOP]'
    - '[TIENDA]'
* settings
  * dynamic-updates: if off, informational signs are only updated when
    tapped.


### Permission Nodes:

* runepvp.cmd: Give players access to RunePvP command
* runepvp.cmd.stats: Stats sub command
* runepvp.cmd.stats.other: View stats of other players
* runepvp.cmd.top: View rankings
* runepvp.signs.place: Allow users to place RunePvP signs
* runepvp.signs.place.stats: Allow users to place RunePvP signs for Stats
* runepvp.signs.place.rankings: Allow users to place RunePvP signs for
  Rankings
* runepvp.signs.place.onlineranks: Allow users to place RunePvP signs
  for Online ranks
* runepvp.signs.place.shop: Allow users to place RunePvP signs for shops
* runepvp.signs.place.casino: Allow users to place RunePvP signs for
  Casinos
* runepvp.signs.use: Allow users to use RunePvP signs
* runepvp.signs.use.stats: Allow users to use stats RunePvP signs
* runepvp.signs.use.rankings: Allow users to use specific RunePvP signs
* runepvp.signs.use.onlineranks: Allow users to use specific RunePvP signs
* runepvp.signs.use.shop: Allow users to use specific RunePvP signs
* runepvp.signs.use.casino: Allow users to use specific RunePvP signs

Changes
-------

* 1.1.0 : More economy support
  * Some code fixes and removed some debug code that slipped through
  * Added support for Economy and MassiveEconomy.
  * Added top (rankings) command
  * Added signs
* 1.0.0 : First submission

Copyright
---------

    RunePvP
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
