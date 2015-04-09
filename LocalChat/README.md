LocalChat
=======

* Summary: Chatting is localized to a certain area
* PocketMine-MP version: 1.4 - API 1.10.0
* DependencyPlugins: -
* OptionalPlugins: -
* Categories: Chat, Fun, Mechanics
* Plugin Access: Commands
* WebSite: [github](https://github.com/alejandroliu/pocketmine-plugins/tree/master/LocalChat)

Overview
--------

Make chats local to a certain area.  You can only talk to players that
are near you.  Far away players may be able to overhear parts of your
conversation.  Even further away players are not able to hear anything.

If you want to broadcast a message to all players in the Level use:

	.text

While if you want to broacast a message to all players in the Server
use:

	:text


Documentation
-------------

The rationale for this plugin is to add a level of distance to in-game
chats.  So if you want to talk to somebody you need to find them and
get close enough to them.

Also, you can spy in other peoples conversation, but you need to be
close enough.  If you are very close, you can hear everything.  The
farther away, you may not hear very well what is being said and the
text becomes garbled.

After certain distance you can't hear anything that is being said.

By prefixing your text with a "." to a message you can *shout* your
message to everybody in the same level.

By prefixing your text with a ":" to a message you can *broadcast*
your message to everybody in the same server.

### Configuration

These can be configured from `config.yml`:

	settings:
	  near: 10
	  far: 20

You can hear all players that are up to `near` blocks away.
You can overhear players that are up to `far` blocks away.


### Permission Nodes:

* localchat.brodcast: Allow access to `.` and `:` to broadcast messages.
* localchat.brodcast.level: Allow access to `.` messages
* localchat.brodcast.server: Allow access to `:` messages

### TODO


Changes
-------

* 1.0.2 : Minor fixes
  * Fixed mkdir warning
* 1.0.1 : Minor update
  * Fixed bug where players don't see their own chat messages.
* 1.0.0 : First public release

Copyright
---------

    LocalChat
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
