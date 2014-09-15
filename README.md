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
eecli generate:command --options --arguments your_command

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

### Create Channel

Creates an ExpressionEngine channel. Pass in a channel short name using underscores only and optionally pass in a channel title. If you exclude the channel title, one will be auto-generated from your channel short name.

```
# create a channel with the short name test_channel
eecli create:channel test_channel

# create a channel with the title Test Channel
eecli create:channel test_channel "Test Channel"

# create a channel with field group 5
eecli create:channel --field_group=5 test_channel

# create a channel with status group 5
eecli create:channel --status_group=5 test_channel

# create a channel with cat group 5 and 6
eecli create:channel --cat_group="5|6" test_channel
```

### Create Snippet

```
# create a blank snippet
eecli create:snippet your_snippet_name

# create a snippet with content
eecli create:snippet your_snippet_name "your snippet content"

# pipe in content
echo "your snippet content" | eecli create:snippet --stdin your_snippet_name

# create a snippet accessible to all sites
eecli create:snippet --global your_snippet_name
```

When you have [Sync Snippets](https://github.com/rsanchez/sync_snippets) installed and configured, this command will write a snippet file as well.

### Create Global Variable

```
# create a blank global variable
eecli create:global_variable your_global_variable_name

# create a global variable with content
eecli create:global_variable your_global_variable_name "your global variable content"

# pipe in content
echo "your global variable content" | eecli create:global_variable --stdin your_global_variable_name
```

When you have [Sync Snippets](https://github.com/rsanchez/sync_snippets) installed and configured, this command will write a global variable file as well.

### Create Template

```
eecli create:template site/index

# multiple templates
eecli create:template site/index site/foo

# with php enabled
eecli create:template --php site/index

# with php enabled on input
eecli create:template --php --input site/index

# with caching on (for 300 seconds)
eecli create:template --cache=300 site/index

# protect javascript
eecli create:template --protect_js site/index

# set a type: webpage, feed, css, js, static, xml
eecli create:template --type=xml site/index
```

### Create Template Group

```
eecli create:template_group site

# multiple groups
eecli create:template_group site news blog

# create the default group
eecli create:template_group --default site
```

### Delete Snippet

```
eecli delete:snippet your_snippet_name

# delete a snippet accessible to all sites
eecli delete:snippet --global your_snippet_name

# delete multiple snippets
eecli delete:snippet your_snippet_name your_other_snippet_name
```

When you have [Sync Snippets](https://github.com/rsanchez/sync_snippets) installed and configured, this command will delete the snippet file as well.

### Delete Global Variable

```
eecli delete:global_variable your_global_variable_name

# delete multiple global variables
eecli delete:global_variable your_global_variable_name your_other_global_variable_name
```

When you have [Sync Snippets](https://github.com/rsanchez/sync_snippets) installed and configured, this command will delete the global variable file as well.

### Delete Template

```
eecli delete:template site/index

# multiple templates
eecli delete:template site/index site/foo
```

### Delete Template Group

```
eecli delete:template_group site

# multiple groups
eecli delete:template_group site news blog
```

### Synchronize Templates

Synchronize the template database with your template files.

```
eecli sync:templates
```

### Synchronize Snippets

Synchronize the snippets database with your snippet files. Requires [Sync Snippets](https://github.com/rsanchez/sync_snippets).

```
eecli sync:snippets
```

### Synchronize Global Variables

Synchronize the global variables database with your global variable files. Requires [Sync Snippets](https://github.com/rsanchez/sync_snippets).

```
eecli sync:global_variables
```

### Synchronize Specialty Templates

Synchronize the specialty templates database with your specialty template files. Requires [Sync Snippets](https://github.com/rsanchez/sync_snippets).

```
eecli sync:specialty_templates
```

### Show Config

Show config items.

```
# Show all config items in a table
eecli show:config

# Show the specified config item
eecil show:config <key>
```

### Show Templates

List all templates found in the database

```
eecli show:templates
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

- `create:category`
- `create:category_group`
- `create:member`
- `create:member_group`
- ~~`create:field`~~*
- `create:field_group`
- ~~`create:low_variable`~~*
- ~~`sync:fields`~~*

\* *Probably not possible.*

