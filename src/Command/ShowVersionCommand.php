<?php

namespace eecli\Command;

class ShowVersionCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'show:version';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Display the EE version.';


    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $this->line(sprintf('<info>ExpressionEngine</info> version <comment>%s</comment>', APP_VER));
    }
}
