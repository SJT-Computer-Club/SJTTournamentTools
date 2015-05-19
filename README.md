# SJTTournamentTools

SJTTournamentTools is a [PocketMine-MP] plugin, using the new API, that provides a set of tools for running Minecraft tournaments, including archery, parkour, building challenges and treasure hunts.

  - Provides location management, so locations can be named and stored to a configuration file
  - Provides teleportation of users to named locations
  - Provides an 'Archery' game where players have a set time to score points firing arrows at a target
  - Provides a 'Build' game where players have a set time to build anything they like within a small area of the world
  - Provides a 'Parkour' game where players must scramble over an obstacle course and touch blocks to score
  - Provides a 'Treasure Hunt' game where players must find blocks placed in random locations throughout the world
  - Disables block creation and destruction, except for ops and for the build areas when a 'Build' game is running
  - Disables lava and water creation, and TNT blocks


### Version
0.2

### Usage

The plugin supports the following commands:

```yaml
    addlocation:
        description: "Adds a Location to the configuration"
        usage: "/addlocation <locationname>"
    tu:
        description: "Teleport a player to a location"
        usage: "/tu <playername> <locationname>"
    archery:
        description: "Set up a game of archery"
        usage: "/archery"
    build:
        description: "Set up a game of build"
        usage: "/build"
    parkour:
        description: "Set up a game of parkour"
        usage: "/parkour"
    treasurehunt:
        description: "Set up a game of treasure hunt"
        usage: "/treasurehunt"
    gamestart:
        description: "Start the game"
        usage: "/gamestart"
    gamestop:
        description: "Stop the current game"
        usage: "/gamestop"
```

### Tech

SJTTournamentTools is written as a [PocketMine-MP] plugin in PHP, using the new PocketMine-MP 1.4 API, so will only work on PocketMine-MP Alpha_1.4 (API version 1.11.0) and above.  The latest version of PocketMine-MP is recommended.  Currently no third party libraries are used by this plugin.

### Installation

To run this plugin during development (i.e. non-Phar), first install the Official DevTools plugin. Instructions for setting up a development plugin environment are here: https://github.com/PocketMine/Documentation/wiki/Plugin-Tutorial

For development, clone the plugin code to `[PocketMine Folder]/plugins/SJTTournamentTools`:

```sh
$ cd [PocketMine Folder]/plugins/
$ git clone [git-repo-url] SJTTournamentTools
```

For production, install the phar file at `[PocketMine Folder]/plugins/SJTTournamentTools.phar`

On first run, the plugin will create a folder `[PocketMine Folder]/plugins/SJTTournamentTools` and will save a local copy of its configuration file there.


### Todo's

See the [GitHub issues page].


License
----

MIT


[PocketMine-MP]:http://www.pocketmine.net/
[GitHub issues page]:../../issues
