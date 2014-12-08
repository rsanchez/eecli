# eecli

`eecli` is a command line interface for ExpressionEngine. It can be used to:

* aid in development, like creating new channels or generating a custom addon.
* run post-deployment tasks on your staging/production server(s), like clearing cache.
* automate critical tasks, like database backup
* build your own custom CLI commands, like import scripts or cron jobs
* debug using an interactive shell

![Screencast of a few example commands](https://github.com/rsanchez/eecli/wiki/images/home.gif)

## Installation

The preferred installation method is to install globally with [Composer](https://getcomposer.org/). Refer the official composer documentation for more information on [installing Composer globally](https://getcomposer.org/doc/00-intro.md#globally). Run this at the command line:

```
$ composer global require eecli/eecli
```

Make sure your global Composer installation's bin folder is added to your PATH in your `~/.bash_profile` (or `~/.profile` or `~/.bashrc` or `~/.zshrc`) so that you may run the binary `eecli` from the command line:

```
export PATH=~/.composer/vendor/bin:$PATH
```

For more installation methods, please see the [full Installation guide](https://github.com/rsanchez/eecli/wiki/Installation) in the Wiki.

## Usage

```
$ eecli <command> [options] [argument1] [argument2]
```

To see a list of available commands, simply type eecli at the root of your project installation:

```
$ eecli
```

For detailed information on a specific command, use the help command:

```
$ eecli help <command>
```

To generate a new config file, use the init command:

```
$ eecli init
```

## Documentation

For more details on installation, configuration and a command reference, see the [Wiki](https://github.com/rsanchez/eecli/wiki).

## Requirements

* PHP 5.3+
* ExpressionEngine 2.5+

## Contributing

See [CONTRIBUTING](https://github.com/rsanchez/eecli/blob/master/CONTRIBUTING.md) for mor information.

## License

`eecli` is released under the MIT License. See the bundled [LICENSE file](https://github.com/rsanchez/eecli/blob/master/LICENSE.txt).