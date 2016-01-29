<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Propel\Communication\Console;

use Spryker\Shared\Config;
use Spryker\Shared\Propel\PropelConstants;
use Spryker\Zed\Console\Business\Model\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class BuildSqlConsole extends Console
{

    const COMMAND_NAME = 'propel:sql:build';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Build SQL with Propel2');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->info('Build sql');

        $config = Config::get(PropelConstants::PROPEL);
        $command = 'vendor/bin/propel sql:build --config-dir '
            . $config['paths']['phpConfDir']
            . ' --schema-dir ' . $config['paths']['schemaDir'];

        $process = new Process($command, APPLICATION_ROOT_DIR);

        return $process->run(function ($type, $buffer) {
            echo $buffer;
        });
    }

}