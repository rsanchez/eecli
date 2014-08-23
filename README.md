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

## Configuration

Run `eecli init` to create a `.eecli.php` config file in the current working directory.

Open `.eecli.php` and follow the instructions found in the file's comments to configure your EE setup.

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

## Custom Commands

You can add custom commands to your `.eecli.php` config file by adding the class name to the 'commands' array.

Here is a simple example Command:

```php
<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveBannedMembersCommand extends Command
{
    protected function configure()
    {
        $this->setName('delete_banned_members');
        $this->setDescription('Removes members that are banned.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ee()->db->delete('members', ['group_id' => 2]);

        $output->writeln('<info>Banned members removed.</info>');
    }
}
```

And your configuration would be:

```php
'commands' => [
    'RemoveBannedMembersCommand',
],
```

You may also use a callback to instantiate your object, useful if you need to inject dependencies.

```php
'commands' => [
    function($app, $config) {
        return new CustomCacheClearingCommand(new RedisClient);
    },
],
```
