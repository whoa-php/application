<?php

/*
 * Copyright 2015-2020 info@neomerx.com
 * Modification Copyright 2021-2022 info@whoaphp.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Whoa\Application\Commands;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Whoa\Application\Data\BaseMigrationRunner;
use Whoa\Application\Data\FileMigrationRunner;
use Whoa\Application\Data\FileSeedRunner;
use Whoa\Application\Packages\Data\DataSettings;
use Whoa\Contracts\Commands\CommandInterface;
use Whoa\Contracts\Commands\IoInterface;
use Whoa\Contracts\Settings\Packages\DataSettingsInterface;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @package Whoa\Application
 */
class DataCommand implements CommandInterface
{
    /**
     * Command name.
     */
    public const NAME = 'w:db';

    /** Argument name */
    public const ARG_ACTION = 'action';

    /** Command action */
    public const ACTION_MIGRATE = 'migrate';

    /** Command action */
    public const ACTION_SEED = 'seed';

    /** Command action */
    public const ACTION_ROLLBACK = 'rollback';

    /** Option name */
    public const OPT_PATH = 'path';

    /**
     * @inheritdoc
     */
    public static function getName(): string
    {
        return static::NAME;
    }

    /**
     * @inheritdoc
     */
    public static function getDescription(): string
    {
        return 'Migrates and seeds application data.';
    }

    /**
     * @inheritdoc
     */
    public static function getHelp(): string
    {
        return 'This command migrates, seeds and resets application data.';
    }

    /**
     * @inheritdoc
     */
    public static function getArguments(): array
    {
        $migrate = static::ACTION_MIGRATE;
        $seed = static::ACTION_SEED;
        $rollback = static::ACTION_ROLLBACK;

        return [
            [
                static::ARGUMENT_NAME => static::ARG_ACTION,
                static::ARGUMENT_DESCRIPTION => "Action such as `$migrate`, `$seed` or `$rollback` data.",
                static::ARGUMENT_MODE => static::ARGUMENT_MODE__REQUIRED,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getOptions(): array
    {
        return [
            [
                static::OPTION_NAME => static::OPT_PATH,
                static::OPTION_DESCRIPTION => 'Path to a list of migrations or seeds. ' .
                    'If not given a path from settings will be used.',
                static::OPTION_SHORTCUT => 'i',
                static::OPTION_MODE => static::OPTION_MODE__REQUIRED,
            ],
        ];
    }

    /**
     * @inheritdoc
     * @param ContainerInterface $container
     * @param IoInterface $inOut
     * @throws ContainerExceptionInterface
     * @throws DBALException
     * @throws InvalidArgumentException
     * @throws NotFoundExceptionInterface
     */
    public static function execute(ContainerInterface $container, IoInterface $inOut): void
    {
        (new static())->run($container, $inOut);
    }

    /**
     * @param ContainerInterface $container
     * @param IoInterface $inOut
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException
     * @throws DBALException
     */
    protected function run(ContainerInterface $container, IoInterface $inOut): void
    {
        $arguments = $inOut->getArguments();
        $options = $inOut->getOptions();

        /** @var SettingsProviderInterface $provider */
        $provider = $container->get(SettingsProviderInterface::class);
        $settings = $provider->get(DataSettings::class);

        $path = $options[static::OPT_PATH] ?? false;
        $action = $arguments[static::ARG_ACTION];
        switch ($action) {
            case static::ACTION_MIGRATE:
                $path = $path !== false ? $path : $settings[DataSettingsInterface::KEY_MIGRATIONS_LIST_FILE] ?? '';
                $this->createMigrationRunner($inOut, $path)->migrate($container);
                break;
            case static::ACTION_ROLLBACK:
                $path = $path !== false ? $path : $settings[DataSettingsInterface::KEY_MIGRATIONS_LIST_FILE] ?? '';
                $this->createMigrationRunner($inOut, $path)->rollback($container);
                break;
            case static::ACTION_SEED:
                $path = $path !== false ? $path : $settings[DataSettingsInterface::KEY_SEEDS_LIST_FILE] ?? '';
                $seedInit = $settings[DataSettingsInterface::KEY_SEED_INIT] ?? null;
                $this->createSeedRunner($inOut, $path, $seedInit)->run($container);
                break;
            default:
                $inOut->writeError("Unsupported action `$action`." . PHP_EOL);
                break;
        }
    }

    /**
     * @param IoInterface $inOut
     * @param string $path
     * @return FileMigrationRunner
     */
    protected function createMigrationRunner(IoInterface $inOut, string $path): FileMigrationRunner
    {
        return new FileMigrationRunner($inOut, $path);
    }

    /**
     * @param IoInterface $inOut
     * @param string $seedsPath
     * @param callable|null $seedInit
     * @param string $seedsTable
     * @return FileSeedRunner
     */
    protected function createSeedRunner(
        IoInterface $inOut,
        string $seedsPath,
        callable $seedInit = null,
        string $seedsTable = BaseMigrationRunner::SEEDS_TABLE
    ): FileSeedRunner {
        return new FileSeedRunner($inOut, $seedsPath, $seedInit, $seedsTable);
    }
}
