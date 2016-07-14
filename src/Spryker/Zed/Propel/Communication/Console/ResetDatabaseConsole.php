<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Propel\Communication\Console;

use Spryker\Shared\Config\Config;
use Spryker\Shared\Propel\PropelConstants;
use Spryker\Zed\Console\Business\Model\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ResetDatabaseConsole extends Console
{

    const COMMAND_NAME = 'propel:database:reset';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription('Reset database');

        parent::configure();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->info('Resetting Database');

        if (Config::get(PropelConstants::ZED_DB_ENGINE) === Config::get(PropelConstants::ZED_DB_ENGINE_PGSQL)) {
            $this->resetPostgresDatabase();
        } else {
            $this->resetMysqlDatabase();
        }
    }

    /**
     * @throws \Exception
     *
     * @return void
     */
    protected function resetPostgresDatabase()
    {
        $databaseExists = $this->existsPostgresDatabase();
        if (!$databaseExists) {
            putenv(sprintf(
                'PGPASSWORD=%s',
                Config::get(PropelConstants::ZED_DB_PASSWORD)
            ));

            $createDatabaseCommand = sprintf(
                'psql -h %s -p %s -U %s -w -c " make me " %s',
                Config::get(PropelConstants::ZED_DB_HOST),
                Config::get(PropelConstants::ZED_DB_PORT),
                Config::get(PropelConstants::ZED_DB_USERNAME),
                Config::get(PropelConstants::ZED_DB_DATABASE),
                Config::get(PropelConstants::ZED_DB_DATABASE)
            );

            $process = new Process($createDatabaseCommand);
            $process->run();

            putenv('PGPASSWORD=');

            if (!$process->isSuccessful()) {
                throw new \RuntimeException($process->getErrorOutput());
            }
        }
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    protected function existsPostgresDatabase()
    {
        putenv(sprintf(
            'PGPASSWORD=%s',
            Config::get(PropelConstants::ZED_DB_PASSWORD)
        ));

        $databaseExistsCommand = sprintf(
            'psql -h %s -p %s -U %s -w -lqt %s | cut -d \| -f 1 | grep -w %s | wc -l',
            Config::get(PropelConstants::ZED_DB_HOST),
            Config::get(PropelConstants::ZED_DB_PORT),
            Config::get(PropelConstants::ZED_DB_USERNAME),
            Config::get(PropelConstants::ZED_DB_DATABASE),
            Config::get(PropelConstants::ZED_DB_DATABASE)
        );

        $process = new Process($databaseExistsCommand);
        $process->run();

        putenv('PGPASSWORD=');

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        $returnValue = (int)$process->getOutput();
        return (bool)$returnValue;
    }

    /**
     * @throws \Exception
     *
     * @return void
     */
    private function resetMysqlDatabase()
    {
        $connection = new \PDO(
            Config::get(PropelConstants::ZED_DB_ENGINE)
            . ':host='
            . Config::get(PropelConstants::ZED_DB_HOST)
            . ';port=' . Config::get(PropelConstants::ZED_DB_PORT),
            Config::get(PropelConstants::ZED_DB_USERNAME),
            Config::get(PropelConstants::ZED_DB_PASSWORD)
        );

        $query = 'CREATE DATABASE IF NOT EXISTS ' . Config::get(PropelConstants::ZED_DB_DATABASE) . ' CHARACTER SET "utf8"';
        $connection->exec($query);
    }

}