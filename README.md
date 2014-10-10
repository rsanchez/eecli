# eecli

`eecli` is a command line interface for ExpressionEngine. It can be used to:

* aid in development, like creating new channels or generating a custom addon.
* run post-deployment tasks on your staging/production server(s), like clearing cache.
* automate critical tasks, like database backup
* build your own custom CLI commands, like import scripts or cron jobs
* debug using an interactive shell

Here are a few example commands:

```
> eecli clear:cache:ee

  EE cache cleared.

> eecli create:template site/about

  Template site/about created.

> eecli generate:addon

  Addon your_addon_name created.

> eecli db:dump

  your_site-201401011200.sql created.

> eecli your_custom_command
```

To learn more, please see the [Wiki](https://github.com/rsanchez/eecli/wiki).