<img src="https://raw.githubusercontent.com/Muirfield/pocketmine-plugins/master/Media/killrate.png" style="width:64px;height:64px" width="64" height="64"/>

<!-- meta:Categories = Informational -->
<!-- meta:PluginAccess = Commands, Databases, Entities, Tiles -->
<!-- template: gd2/header.md -->

# KillRate

- Summary: Keep track of the number of kills
- PocketMine-MP version: 1.5 (API:1.12.0)
- DependencyPlugins: 
- OptionalPlugins: PocketMoney, MassiveEconomy, EconomyAPI, GoldStd, RankUp
- Categories: Informational 
- Plugin Access: Commands, Databases, Entities, Tiles 
- WebSite: https://github.com/Muirfield/pocketmine-plugins/tree/master/KillRate

<!-- end-include -->

## Overview

<!-- php: $v_forum_thread = "http://forums.pocketmine.net/threads/killrate.8060/"; -->
<!-- template: prologue.md -->

**DO NOT POST QUESTIONS/BUG-REPORTS/REQUESTS IN THE REVIEWS**

It is difficult to carry a conversation in the reviews.  If you
have a question/bug-report/request please use the
[Thread](http://forums.pocketmine.net/threads/killrate.8060/) for
that.  You are more likely to get a response and help that way.

_NOTE:_

This documentation was last updated for version **2.1.1**.

Please go to
[github](https://github.com/Muirfield/pocketmine-plugins/tree/master/KillRate)
for the most up-to-date documentation.

You can also download this plugin from this [page](https://github.com/Muirfield/pocketmine-plugins/releases/tag/KillRate-2.1.1).

<!-- end-include -->

This plugin keeps track on how much killing is going-on on a server.

It may optionally use an economy plugin like for example, PocketMoney,
to reward killing.

Basic Usage:

* killrate - Will show your KillRate stats.
* killrate stats [player] - Show the KillRate stats for [player]
* killrate top [online] - Show top players.

You can also place signs to show game statistics.

## Documentation

This plugin supports PocketMoney, GoldStd, MassiveEconomy and EconomysAPI.

### Signs

You can place signs showing current game statistics.  The following
sign types are available by default, by entering the keyword
([KEYWORD]) in **LINE1** of the sign:

* [STATS] - Current player statistics
* [RANKINGS] - Top 3 players + scores
* [ONLINE TOPS] - Top 3 on-line players + scores
* [RANKNAMES] - Top 3 player names
* [RANKPOINTS] - Top 3 player scores
* [TOPNAMES] - Top 3 on-line player names
* [TOPPOINTS] - Top 3 on-line player scores

Signs showing top players can be further customized by adding
additional entries in the sign text:

* LINE2 - Title, this will be the first line of the sign.  If,
  however, you set it to **"^^^"** (Three consecutive **^** signs),
  the title will be omitted (and the sign will show a top 4).
* LINE3 - What statistic to count.  It defaults to _points_, but it
  can be changed to anything (for example, _deaths_, _player_, etc).
  Essentially the value here is the word on the left when you enter
  the command _killrate stats_.
* LINE4 - format line, select a format out of the **config.yml**
  file's **formats** section.

In the **formats** section you have:

```
  selector: format
```

The **selector** is a word that matches the text in **LINE4** of the
sign.  The format can contain any text and the following variable
substitutions:

* {player} - player's name
* {n} - rank number
* {count} - score
* {sname} - only the first 8 characters of the player's name

### RankUp Support

To enable the **ranks** feature, the
[RankUp](http://forums.pocketmine.net/plugins/rankup.830/)
plugin is required.  This provides a leveling up functionality.  The following
settings are recommended to be configured in RankUp's **config.yml.**

* preferred-economy: null
  - This disables the economy support.  **KillRate** will be ranking up, so
    you don't need to buy ranks.
* ranks: Define your ranks as needed.  Also the price for each rank are
  interpreted by **KillRate** as the amount of points needed to rank up.

Also, you should remove the permission **rankup.rankup**.  This is used for
the command to buy ranks.  This is not needed as ranks are awarded by
**KillRate** automatically.

### kill-streak

The kill streak feature is used to track kill-streaks.  If you enable you
also need to define the **min-kills** in **settings**.  If a player reaches
this many victories in a row without dying he will be in a kill-streak and
an additional bonus money gets awarded.

<!-- php:$h=3; -->
<!-- template: gd2/permissions.md -->

### Permission Nodes

* killrate.cmd: Give players access to KillRate command
* killrate.cmd.stats: Access to stats command
* killrate.cmd.stats.other (op): View other's stats
* killrate.cmd.rank: View top players
* killrate.cmd.give (op): Give points to players
* killrate.signs.place (op): Allow to place KillRate signs
* killrate.signs.use: Allow to use KillRate signs

<!-- end-include -->

### Configuration

Configuration is through the **config.yml** file.  The following sections
are defined.

<!-- php:$h=4; -->
<!-- template: gd2/cfg.md -->
#### database

*  backend: Use SQLiteMgr or MySqlMgr
*  MySql: MySQL settings. Only used if backend is MySqlMgr to configure MySql settings

#### features

*  signs: enable/disable signs
*  ranks: Enable support for RankUp plugin
*  achievements: Enable PocketMine achievements
*  kill-streak: Enable kill-streak tracking. tracks the number of kills without dying
*  rewards: award money. if true, money is awarded.  Requires an economy plugin

#### formats

Sign formats used to show sign data.

#### settings

*  points: award points. if true points are awarded and tracked.
*  min-kills: Minimum number of kills before declaring a kill-streak
*  reset-on-death: Reset counters on death. Set to false to disable, otherwise the number of deaths till reset. When the player dies X number of times, scores will reset.  (GAME OVER MAN!)
*  creative: track creative kills. if true, kills done by players in creative are scored
*  dynamic-updates: Update signs. Set to 0 or false to disable, otherwise sign update frequence in ticks
*  default-rank: Default rank (when resetting ranks) set to **false** to disable this feature

#### signs

Placed signs text.
These are used to configure sign texts.  Place signs with the
words on the left, and the sign type (on the right) will be
created

#### values


Configure awards for the different type of kills.  Format:

    "entity": [ money, points ]

The entity ( * ) is the default.


<!-- end-include -->

<!-- template: gd2/mctxt.md -->

## Translations

This plugin will honour the server language configuration.  The
languages currently available are:

* English
* Spanish


You can provide your own message file by creating a file called
**messages.ini** in the plugin config directory.
Check [github](https://github.com/Muirfield/pocketmine-plugins/tree/master/KillRate/resources/messages/)
for sample files.
Alternatively, if you have
[GrabBag](http://forums.pocketmine.net/plugins/grabbag.1060/) v2.3
installed, you can create an empty **messages.ini** using the command:

     pm dumpmsgs KillRate [lang]

<!-- end-include -->

## API

This plugins implements an API.  Please go to
[API docs](http://Muirfield.github.io/pocketmine-plugins/apidocs/index.html)
to read the API reference documentation.

Example Usage:

### Check API availability

```php
$api = null;
if (($plugin = $server->getPluginManager()->getPlugin("KillRate") !== null) &&
      $plugin->isEnabled() &&
      MPMU::apiCheck($plugin->getDescription()->getVersion(),"2.0")) {
  $api = $plugin->api;
}
```

### Call an API function:

```php
$score = $api->getScore($player);
```

## KillRateEx

There is a script extension for KillRate that implements Levels in KillRate
called KillRateEx.

It is not as plug and play as KillRate so it is only available as a script
plugin.  This is because it requires to be customized before use.

KillRateEx can be downloaded from
[github](https://github.com/Muirfield/pocketmine-plugins/tree/master/KillRate)

Some versions of KillRate will create a sample KillRateEx.php in the KillRate
folder.  You then only need to copy that file to your Plugins folder.  That
version may be out-of-date, so preferably you should download it from the link
shown earlier.

In order to use the script extension you need to do the following:

1. Download the script plugin:
   [KillRateEx.php](https://github.com/Muirfield/pocketmine-plugins/tree/master/KillRate/example)
2. Copy the script plugin to your plugin folder.
3. Install [PurePerms](http://forums.pocketmine.net/plugins/pureperms.862/)
4. Read KillRateEx.php on how to configure PurePerms or alternatively download
   and use the example [PurePerms-groups.yml](https://github.com/Muirfield/pocketmine-plugins/tree/master/KillRate/example)
   and place it in the PurePerms folder as "groups.yml".
5. Read and modify KillRateEx.php according to taste.  The script has plenty
   of comments on how things work.
6. Re-start your server.

## TODO

* getSysVarsV1 : should cache values.
  - death-dealer should expire cache.
  - getSysVars when called will check cache and return.
  - if cache is expire, we calculate.
  - Alternatively, getRankings should do the caching....

## FAQ

* Q: Can you score when you push people to lava (or other indirect kills)?
* A: Only direct kills are scored.  All indirect kills (pushing people
  to lava, causing explosions, etc) can not be scored.

# Changes

* ??
  * Closes #35, bug reported by @legoboy0215
* 2.1.1: Bug fixes
  * Fixed bug reported by @PolarKing
* 2.1.0: Ranks
  * Config file layout changed
  * Added more achievements
  * Added more events
  * Added support for RankUp (Suggested by @rock2rap)
  * Code clean-ups
  * Thanks to @rock2rap for helping test it
* 2.0.1: Bug fixes
  * Removed KillRateEx inclusion
  * dump messages.ini if no language defined.
  * Fixed crash when not permitted to place signs (Reported by @Tolo)
* 2.0.0: Partial rewrite
  * Fixed bug prevents scoring on creative
  * Fixed bug related to libcommon MoneyAPI (crash when no Economy loaded)
  * Dropping support for PocketMine v1.4 and lower
  * API has been revamped!
  * Included example extension
  * Added Kill/Death Ratio calculations
* 1.2.3:
  * MySqlMgr: Fixed typo
  * Fixed bug with setting rewards/points to false (Reported by @reidq7)
* 1.2.2
  * small tweaks on the comments of the config file...
  * Added achievements
* 1.2.1:
  * Requested by @CaptainKenji17:
    * Double money and Best streak tracking.
  * Fixed MySql support.  It should work now.
* 1.2.0: Bumped the version number to reflect config changes.
  * Added the "^^^" hack.
  * Removed pop-up scores.
  * Improved doc-umentation
* 1.1.1:
  * Minor tweaks
  * Signs are more configurable
  * **PLEASE DELETE OLD CONFIG.YML FILE**
* 1.1.0: General improvements
  * Added experimental MySQL support (@predawnia)
  * Messages file and translations: spanish (@Daniel123)
  * Dynamic signs
  * Pop-up scores
  * Kill Streak
  * Reset scores on deaths
* 1.0.2 : Arrow
  * Improved scoring of Exploding arrows
  * Fixed a bug in the way we call the EconomyAPI
* 1.0.1 : Bugfixes
  * Removed warnings
  * Improve the scoring detection
  * Scores deaths
  * Added support for GoldStd
* 1.0.0 : First submission

<!-- php:$copyright="2015"; -->
<!-- template: gd2/gpl2.md -->
# Copyright

    KillRate
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

