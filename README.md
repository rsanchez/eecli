# eecli

Command line interface for ExpressionEngine

## Installation

```
composer global require eecli/eecli dev-master
```

## Configuration

Copy (`sample.eecli.php`)[https://github.com/rsanchez/eecli/blob/master/sample.eecli.php] to the root of your EE installation with the filename `.eecli.php`.

Follow the instructions found in the file's comments to configure your EE setup.

## Usage

### Clear EE Cache

Clears the native EE cache(s).

```
# clear all EE caches
eecli cache:clear:ee

# clear EE page caches
eecli cache:clear:ee page

# clear EE db caches
eecli cache:clear:ee db

# clear EE tag caches
eecli cache:clear:ee tag
```

### Clear Stash Cache

Clears the entire Stash cache.

```
eecli cache:clear:stash
```

### Clear CE Cache

Clears CE Cache.

```
# clear all CE Cache drivers
eecli cache:clear:ce_cache

# clear specific CE Cache items
eecli cache:clear:ce_cache local/blog/detail/foo local/blog/detail/bar

# clear specific CE Cache tags
eecli cache:clear:ce_cache --tags foo bar
```

### Github Addon Installer

If you have [Github Addon Installer](https://github.com/rsanchez/github_addon_installer) installed, you can use the `install` command.

```
eecli install
```

This will prompt you to enter an addon name. Start typing to trigger autocomplete.

You may also simply specify the addon name in the command. You can specify a branch as the second argument.

```
eecli install low_replace
eecli install stash dev
```
