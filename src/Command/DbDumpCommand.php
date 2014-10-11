<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;

class DbDumpCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'db:dump';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Dump your database using mysqldump.';

    /**
     * {@inheritdoc}
     */
    protected function getOptions()
    {
        return array(
            array(
                'name',
                null,
                InputOption::VALUE_OPTIONAL,
                'Change the name of the file from the default.',
            ),
            array(
                'gzip',
                null,
                InputOption::VALUE_NONE,
                'Compress the backup file with on gzip.',
            ),
            array(
                'backups',
                null,
                InputOption::VALUE_OPTIONAL,
                'Keep only the specified number database dump files, delete the rest.',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'path',
                InputArgument::OPTIONAL,
                'Where to create the db dump file.',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        ee()->load->helper('security');

        // where to create the file, default to current dir
        $path = $this->argument('path') ?: '.';

        $path = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        $gzip = $this->option('gzip');

        if (! function_exists('system')) {
            throw new \RuntimeException('The system function is disabled php.');
        }

        if ($gzip && ! system('which gzip')) {
            throw new \RuntimeException('gzip could not be found in your $PATH.');
        }

        if ($gzip && ! system('which mysqldump')) {
            throw new \RuntimeException('mysqldump could not be found in your $PATH.');
        }

        $extension = $gzip ? '.sql.gz' : '.sql';

        $name = $this->option('name');

        // set a default name <db>[-<env>]-<yyyymmddhhmmss>
        if (! $name) {

            $name = sanitize_filename(ee()->db->database);

            $env = $this->getApplication()->getEnvironment();

            if ($env) {
                $name .= '-'.$env;
            }

            $name .= '-'.date('YmdHis');
        }

        $file = $path.$name.$extension;

        // compile the mysqldump command using EE's db credentials
        $command = sprintf(
            'MYSQL_PWD="%s" /usr/bin/env mysqldump -u "%s" -h "%s" "%s"%s > %s',
            ee()->db->password,
            ee()->db->username,
            ee()->db->hostname,
            ee()->db->database,
            $gzip ? ' | gzip' : '',
            $file
        );

        $executed = system($command);

        $backups = $this->option('backups');

        // check if we need to delete any old backups
        if (is_numeric($backups)) {

            $finder = new Finder();

            // look for other files in the path that use the
            // sql / sql.gz extension
            $finder->files()
                ->in($path)
                ->name('*'.$extension)
                ->sortByModifiedTime();

            // omit the X most recent files
            $files = array_slice(array_reverse(iterator_to_array($finder)), $backups);

            // if there are backups beyond our limit, delete them
            foreach ($files as $file) {
                unlink($file->getRealPath());
            }
        }

        if ($executed !== false) {
            $this->info($file.' created.');
        } else {
            $this->error('Could not execute mysqldump.');
        }
    }
}
