<img src="https://raw.githubusercontent.com/alejandroliu/pocketmine-plugins/master/Media/GoldStd2-icon.png" style="width:64px;height:64px" width="64" height="64"/>

<!-- meta:Categories = Economy -->
<!-- meta:PluginAccess =  Commands, Entities, Items -->

<!-- template: gd2/header.md -->

# GoldStd

- Summary: Gold based economy plugin
- PocketMine-MP version: 1.5 (API:1.12.0)
- DependencyPlugins:
- OptionalPlugins:
- Categories: Economy
- Plugin Access: Commands, Entities, Items
- WebSite: https://github.com/alejandroliu/pocketmine-plugins/tree/master/GoldStd

<!-- end-include -->

## Overview

<!-- php: $v_forum_thread = "http://forums.pocketmine.net/threads/goldstd.8071/"; -->
<!-- template: prologue.md -->

**DO NOT POST QUESTION/BUG-REPORTS/REQUESTS IN THE REVIEWS**

It is difficult to carry a conversation in the reviews.  If you
have a question/bug-report/request please use the
[Thread](http://forums.pocketmine.net/threads/goldstd.8071/) for
that.  You are more likely to get a response and help that way.

_NOTE:_

This documentation was last updated for version **1.3.0dev1**.

Please go to
[github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/GoldStd)
for the most up-to-date documentation.

You can also download this plugin from this [page](https://github.com/alejandroliu/pocketmine-plugins/releases/tag/GoldStd-1.3.0dev1).

<!-- end-include -->

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

GoldStd supports these types of signs.

1. Shops: Buy goods
2. Gambling: Bet money
3. Trading: Trade goods
4. Effects: Buy potions
5. Command: Pay to have commands executed

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

Examples:

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

### Commands Shop

Tap a sign and the command will be executed.
Place a sign with the following text:

    [CODE]
    [CMD]
    <command-id>
    <price>
    [/CODE]

For this to work you need to have a file in your GoldStd plugin folder with
the name **commands.txt**.  In there you need to put all the possible
commands like this:

    command-id: command to execute

Only one command is possible.  If you need multiple commands and more
features you could use
[GrabBag](https://forums.pocketmine.net/plugins/grabbag.1060/) and write
a _PMScript_.

Examples:

    [CODE]
    # This is the content of the "commands.txt file.
    # cmd-id: command to execute
    clearchat: clearchat
    fly: +op:fly
    heal me: +op:heal
    to spawn: spawn
    script: rc dostuff
    [/CODE]

Signs:

* [CMD]
  * clearchat
  * 1p
* [CMD]
  * fly
  * 20p
  * You can fly!
* [CMD]
  * heal me
  * 5p
  * The Doctor
  * is IN!
* [CMD]
  * to spawn
  * 1p

### API

* API
  - getMoney
  - setMoney
  - grantMoney

### Configuration

Configuration is through the `config.yml` file.

<!-- php:$h=4; -->
<!-- template: gd2/cfg.md -->
#### other-sections

*  trade-goods: List of tradeable goods
*  signs: Text used to identify GoldStd signs

#### settings

*  currency: Item to use for currency false or zero disables currency exchange.
*  signs: set to true to enable shops|casino signs


<!-- end-include -->

<!-- template: gd2/permissions.md -->

### Permission Nodes

* goldstd.cmd.pay: Access to pay command
* goldstd.cmd.balance: Show your current balance
* goldstd.cmd.shopkeep (op): ShopKeepr management
* goldstd.shopkeep.shop: Allow buying from shop keeper
* goldstd.signs.use: Allow access to signs
* goldstd.signs.use.casino: Allow access to casino signs
* goldstd.signs.use.shop: Allow access to shopping signs
* goldstd.signs.use.trade: Allow access to trading signs
* goldstd.signs.use.effects: Allow access to Effects signs
* goldstd.signs.place (op): Allow placing signs
* goldstd.signs.place.casino (op): Allow placing casino signs
* goldstd.signs.place.shop (op): Allow placing shopping signs
* goldstd.signs.place.trade (op): Allow placing trading signs
* goldstd.signs.place.effects (op): Allow placing Effects signs

<!-- end-include -->

<!-- template: gd2/mctxt.md -->

## Translations

This plugin will honour the server language configuration.  The
languages currently available are:

* English
* Spanish


You can provide your own message file by creating a file called
**messages.ini** in the plugin config directory.
Check [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/GoldStd/resources/messages/)
for sample files.
Alternatively, if you have
[GrabBag](http://forums.pocketmine.net/plugins/grabbag.1060/) v2.3
installed, you can create an empty **messages.ini** using the command:

     pm dumpmsgs GoldStd [lang]

<!-- end-include -->

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

## TODO

* Implement shopping by command
* Shopping by ChestShop or Sign?

# Changes

* 1.4.0: Split ShopKeep
  * ShopKeeper code split into ShoppingCart and ShopKeep NPC only (Requested by @iVertx)
* 1.3.0: Command shops
  * Add command shops (Requested by @Kyoyuki)
* 1.2.2: Bug fixes
  * Fixing Effects permissions (reported by @may)
* 1.2.1: Bug fixes
  * Weapons are detected using isSword, isAxe and isPickaxe.
  * Fixed bug that caused inventory to be lost (Thanks @reidq7 for figuring it out)
  * Tweaked the priority of event listeners.
  * Changed MPMU::itemName to ItemName::str
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

<!-- php:$copyright="2015"; -->
<!-- template: gd2/gpl2.md -->
<!-- end-include -->
