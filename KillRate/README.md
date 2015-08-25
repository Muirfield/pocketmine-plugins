<img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/killrate.png" style="width:64px;height:64px" width="64" height="64"/>

# KillRate

* Summary: Keep track of the number of kills
* PocketMine-MP version: 1.5 (API:1.12.0)
* DependencyPlugins: -
* OptionalPlugins: PocketMoney,MassiveEconomy,EconomyAPI,GoldStd
* Categories: Informational
* Plugin Access: Commands, Databases, Entities, Tile Entities
* WebSite: https://github.com/alejandroliu/pocketmine-plugins/tree/master/KillRate

## Overview

<!-- php: $v_forum_thread = "http://forums.pocketmine.net/threads/killrate.8060/"; -->
<!-- template: prologue.md -->

**DO NOT POST QUESTION/BUG-REPORTS/REQUESTS IN THE REVIEWS**

It is difficult to carry a conversation in the reviews.  If you
have a question/bug-report/request please use the
[Thread](http://forums.pocketmine.net/threads/killrate.8060/) for
that.  You are more likely to get a response and help that way.

_NOTE:_

This documentation was last updated for version **2.0.0dev1**.

Please go to
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/KillRate)
for the most up-to-date documentation.

You can also download this plugin from this [page](https://github.com/alejandroliu/pocketmine-plugins/releases/tag/KillRate-2.0.0dev1).

<!-- template-end -->

This plugin keeps track on how much killing is going-on on a server.

It may optionally use an economy plugin like for example, PocketMoney,
to reward killing.

Basic Usage:

* killrate - Will show your KillRate stats.
* killrate stats [player] - Show the KillRate stats for [player]
* killrate top [online] - Show top players.

You can also place signs to show game statistics.

## Documentation

This plugin supports PocketMoney and GoldStd and has experimental
support for MassiveEconomy and EconomyAPI.

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

### Permission Nodes

* killrate.cmd : Give players access to KillRate command
* killrate.cmd.stats : Access to stats command
* killrate.cmd.stats.other : View other's stats
  (Defaults to Op)
* killrate.cmd.rank : View top players
* killrate.signs.place : Allow to place KillRate signs
  (Defaults to Op)
* killrate.signs.use : Allow to use KillRate signs


### Configuration

Configuration is through the `config.yml` file.
The following sections are defined:

#### config.yml

*  settings: Configuration settings
	*  points: award points. if true points are awarded and tracked.
	*  rewards: award money. if true, money is awarded.  Requires an economy plugin
	*  creative: track creative kills. if true, kills done by players in creative are scored
	*  dynamic-updates: Update signs. Set to 0 or false to disable, otherwise sign update frequence in ticks
	*  reset-on-death: Reset counters on death. set to **false** or to a number.  When the player dies that number of times, scores will reset.  (GAME OVER MAN!)
	*  kill-streak: Enable kill-streak tracking. "set to **false** or to a number.  Will show the kill streak of a player once the number of kills before dying reaches number
	*  achievements: Enable PocketMine achievements
*  values: configure awards. (1st.money, 2nd.points) Configures how many points or how much money is awarded per kill type.  The first number is points, the second is money.  You can use negative values.
*  formats: Sign formats. Used to show sign data
*  backend: Use SQLiteMgr or MySqlMgr
*  MySql: MySQL settings. Only used if backend is MySqlMgr to configure MySql settings
*  signs: placed signs text. These are used to configure sign texts.  Place signs with the words on the left, and the sign type (on the right) will be created


## Translations

This plugin will honour the server language configuration.  The
languages currently available are:

* English
* Spanish

You can provide your own message file by creating a file called
`messages.ini` in the plugin config directory.  Check
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/KillRate)
for sample files.

The contents of these "ini" files are key-value pairs:

	"Base text"="Translated Text"

## FAQ

* Q: Can you score when you push people to lava (or other indirect kills)?
* A: Only direct kills are scored.  All indirect kills (pushing people
  to lava, causing explosions, etc) can not be scored.

# TODO

* getSysVars : should cache values.
  - death-dealer should expire cache.
  - getSysVars when called will check cache and return.
  - if cache is expire, we calculate.
  - Alternatively, getRankings should do the caching....

# Changes

* 2.0.0: Partial rewrite
  * Fixed bug prevents scoring on creative
  * Fixed bug related to libcommon MoneyAPI (crash when no Economy loaded)
  * Dropping support for PocketMine v1.4 and lower
  * API has been revamped!
  * Included example extension
  * Added KillRate calculations
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
  * Improved documentation
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

