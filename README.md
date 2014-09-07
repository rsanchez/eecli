# eecli

Command line interface for ExpressionEngine

## Installation

```
composer global require eecli/eecli dev-master
```

Make sure your global composer installation is added to your PATH in your `~/.bash_profile` (or `~/.profile` or `~/.bashrc` or `~/.zshrc`) so that you may run the binary from the command line:

```
export PATH=~/.composer/vendor/bin:$PATH
```

You can also install this locally:

```
composer require eecli/eecli dev-master
```

Then the command would be found in your `vendor/bin` folder, so you'd run this at your command line:

```
vendor/bin/eecli <your command>
```

## Configuration

Run `eecli init` to create a `.eecli.php` config file in the current working directory.

Open `.eecli.php` and follow the instructions found in the file's comments to configure your EE setup.

If your system folder is the default `system` and is in the current working directory, you *may* not need a config file.

You will receive a warning message if your system folder cannot be found.

## Commands

### Help

Display information about a command and its arguments/options.

```
eecli help <command>
```

### List

List the available commands.

```
eecli list
```

### Generate Command

Generate a custom command file. Specify the command name as the first argument.

```
# generates a file called YourCustomCommand in the current directory
eecli generate:command your:custom_comand

# generates in the specified directory
eecli generate:command your:custom_comand ./commands/

# generates with a namespace
eecli generate:command --namespace="YourSite\Command" your:custom_comand ./src/YourSite/Command/

# generates with arguments and options
eecli generate:command --has-options --has-arguments your_command

# generates with a description
eecli generate:command --description="Clear custom cache" cache:clear:custom
```

### Generate .htaccess

Generate the official EE .htaccess file (as found in the [EE documentation](https://ellislab.com/expressionengine/user-guide/urls/remove_index.php.html)).

```
# generates in the current directory
eecli generate:htaccess
```

### Generate Addon(s)

Generate an addon using a wizard interface.

```
eecli generate:addon
```

![Screencast of addon generation](https://rsanchez.github.io/eecli/images/eecli-generate-addon.gif)

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

Clears the entire Stash cache by truncating the `exp_stash` table.

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

### Run Addon Updates

This checks if any of your addons (modules, extensions, and fieldtypes) are out of date by comparing version numbers in your database with version numbers in your addon files. If so, it will run the addon's update method. This is exactly how addon updates work inside the control panel.

```
# run all addon updates
eecli update:addons

# run module updates
eecli update:addons modules

# run extension updates
eecli update:addons extensions

# run fieldtype updates
eecli update:addons fieldtypes

# run accessory updates
eecli update:addons accessories
```

### DB Dump

Dump your database using `mysqldump`. NOTE: your PHP installation must be able to call `mysqldump` via the PHP `system` function. If you have an `ENV` or `ENVIRONMENT` constant defined in your config.php, that name will be used in the sql dump file name.

```
# create a sql dump file in the current folder
eecli db:dump

# create a sql dump file in the specified folder
eecli db:dump backups/

# create a sql dump file, gzipped
eecli db:dump --gzip

# create a sql dump file, keep the last X backups and delete the rest
eecli db:dump --backups=10 --gzip backups/
```

### REPL

```
eecli repl
```

Starts an interactive shell.

### Show Config

Show config items.

```
# Show all config items in a table
eecli show:config

# Show the specified config item
eecil show:config <key>
```

### Github Addon Installer

If you have [Github Addon Installer](https://github.com/rsanchez/github_addon_installer) installed, you can use the `install:addon` command.

```
eecli install:addon
```

This will prompt you to enter an addon name. Start typing to trigger autocomplete.

You may also simply specify the addon name in the command. You can specify a branch as the second argument.

```
eecli install low_replace
eecli install stash dev
```

## Third Party Commands

ExpressionEngine addons can add custom commands to eecli using the `eecli_add_commands` hook:

```php
public function eecli_add_commands($commands, $app)
{
    if (ee()->extensions->last_call !== FALSE)
    {
        $commands = ee()->extensions->last_call;
    }

    require_once PATH_THIRD.'my_addon/src/MyCustomCommand.php';

    $commands[] = new MyCustomCommand();

    return $commands;
}
```

## Autocompletion

If you use [Oh My ZSH](https://github.com/robbyrussell/oh-my-zsh), you can install the [eecli ZSH autocompletion plugin](https://github.com/rsanchez/eecli/tree/zsh-plugin).

## Custom Commands

eecli custom commands are [Laravel Console](http://laravel.com/docs/commands#building-a-command) Command objects, which extend [Symfony Console](http://symfony.com/doc/current/components/console/introduction.html) Command objects. You can add custom commands to your `.eecli.php` config file by adding the class name to the 'commands' array.

You can generate a custom command file using the `eecli generate:command` command.

If your command does not require that EE be bootstrapped to run, you should simply implement the `eecli\Command\ExemptFromBootstrapInterface`, which has no additional methods.

Here is a simple example custom command (it is assumed your custom command classes are in your autoloader):

```php
<?php

namespace MyApp\Command;

use Illuminate\Console\Command;

class RemoveBannedMembersCommand extends Command
{
    protected $name = 'remove_banned_members';
    protected $description = 'Removes members that are banned.';

    protected function fire()
    {
        ee()->db->delete('members', array('group_id' => 2));

        $this->info('Banned members removed.');
    }
}
```

And your configuration would be:

```php
'commands' => array(
    '\\MyApp\\Command\\RemoveBannedMembersCommand',
),
```

Then you could run this do remove banned members, in a cron job for instance.

```
eecli remove_banned_members
```

You may also use a callback to instantiate your object, useful if you need to inject dependencies.

```php
'commands' => [
    function ($app) {
        return new CustomCacheClearingCommand(new RedisClient);
    },
],
```

## Contributing

Please send pull requests to the [develop branch](https://github.com/rsanchez/eecli/tree/develop). Please be sure to follow the [PSR-1](http://www.php-fig.org/psr/psr-1/) coding standard and the [PSR-2](http://www.php-fig.org/psr/psr-2/) style guide.

## Command Wishlist

These commands yet to be implemented. Pull requests welcome.

- `create:channel`
- `create:category`
- `create:category_group`
- `create:template`
- `create:template_group`
- `create:member`
- `create:member_group`
- `create:field`
- `create:field_group`
- `create:global_variable`
- `create:snippet`
- `create:low_variable`
- `sync:fields`
- `sync:templates`
- `sync:snippets`
- `sync:global_variables`
