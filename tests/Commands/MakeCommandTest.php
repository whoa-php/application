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

namespace Whoa\Tests\Application\Commands;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Whoa\Application\Commands\MakeCommand;
use Whoa\Application\Exceptions\InvalidArgumentException;
use Whoa\Container\Container;
use Whoa\Contracts\Commands\IoInterface;
use Whoa\Contracts\FileSystem\FileSystemInterface;
use Whoa\Contracts\Settings\Packages\AuthorizationSettingsInterface;
use Whoa\Contracts\Settings\Packages\DataSettingsInterface;
use Whoa\Contracts\Settings\Packages\FluteSettingsInterface;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Whoa\Tests\Application\TestCase;
use Mockery;
use Mockery\Mock;

/**
 * @package Whoa\Tests\Application
 */
class MakeCommandTest extends TestCase
{
    /**
     * @var Mock
     */
    private $fileSystemMock = null;

    /**
     * @inheritdoc
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->fileSystemMock = Mockery::mock(FileSystemInterface::class);
    }

    /**
     * Test command for make JSON API resource.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testMakeFullResource(): void
    {
        $this->checkOutputs(MakeCommand::ITEM_FULL_RESOURCE, 'Board', 'Boards', [
            [
                'Model.txt' => '{%SINGULAR_CC%},{%SINGULAR_LC%},{%SINGULAR_UC%},{%PLURAL_LC%},{%PLURAL_UC%}',
                DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Board.php' => 'Board,board,BOARD,boards,BOARDS',
            ],
            [
                'Migration.txt' => '{%SINGULAR_CC%},{%PLURAL_CC%}',
                DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . 'BoardsMigration.php' => 'Board,Boards',
            ],
            [
                'Seed.txt' => '{%SINGULAR_CC%},{%PLURAL_CC%}',
                DIRECTORY_SEPARATOR . 'seeds' . DIRECTORY_SEPARATOR . 'BoardsSeed.php' => 'Board,Boards',
            ],
            [
                'Schema.txt' => '{%SINGULAR_CC%},{%PLURAL_LC%}',
                DIRECTORY_SEPARATOR . 'schemas' . DIRECTORY_SEPARATOR . 'BoardSchema.php' => 'Board,boards',
            ],
            [
                'Api.txt' => '{%SINGULAR_CC%},{%PLURAL_CC%},{%SINGULAR_UC%},{%PLURAL_UC%}',
                DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'BoardsApi.php' => 'Board,Boards,BOARD,BOARDS',
            ],
            [
                'QueryRulesOnRead.txt' => '{%SINGULAR_CC%},{%PLURAL_CC%}',
                DIRECTORY_SEPARATOR . 'validators' . DIRECTORY_SEPARATOR .
                'Board' . DIRECTORY_SEPARATOR . 'BoardsReadQuery.php' => 'Board,Boards',
            ],
            [
                'ApiAuthorization.txt' =>
                    '{%SINGULAR_CC%},{%PLURAL_CC%},{%SINGULAR_UC%},{%PLURAL_UC%},{%SINGULAR_LC%}',
                DIRECTORY_SEPARATOR . 'policies' . DIRECTORY_SEPARATOR .
                'BoardRules.php' => 'Board,Boards,BOARD,BOARDS,board',
            ],
            [
                'ValidationRules.txt' => '{%SINGULAR_CC%},{%PLURAL_LC%},{%SINGULAR_LC%}',
                DIRECTORY_SEPARATOR . 'rules' . DIRECTORY_SEPARATOR .
                'Board' . DIRECTORY_SEPARATOR . 'BoardRules.php' => 'Board,boards,board',
            ],
            [
                'JsonRulesOnCreate.txt' => '{%SINGULAR_CC%},{%SINGULAR_LC%}',
                DIRECTORY_SEPARATOR . 'validators' . DIRECTORY_SEPARATOR .
                'Board' . DIRECTORY_SEPARATOR . 'BoardCreateJson.php' => 'Board,board',
            ],
            [
                'JsonRulesOnUpdate.txt' => '{%SINGULAR_CC%},{%SINGULAR_LC%}',
                DIRECTORY_SEPARATOR . 'validators' . DIRECTORY_SEPARATOR .
                'Board' . DIRECTORY_SEPARATOR . 'BoardUpdateJson.php' => 'Board,board',
            ],
            [
                'JsonController.txt' => '{%SINGULAR_CC%},{%PLURAL_CC%}',
                DIRECTORY_SEPARATOR . 'json-controllers' . DIRECTORY_SEPARATOR .
                'BoardsController.php' => 'Board,Boards',
            ],
            [
                'JsonRoutes.txt' => '{%SINGULAR_CC%},{%PLURAL_CC%}',
                DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'BoardApiRoutes.php' => 'Board,Boards',
            ],
            [
                'WebRulesOnCreate.txt' => '{%SINGULAR_CC%}',
                DIRECTORY_SEPARATOR . 'validators' . DIRECTORY_SEPARATOR .
                'Board' . DIRECTORY_SEPARATOR . 'BoardCreateForm.php' => 'Board',
            ],
            [
                'WebRulesOnUpdate.txt' => '{%SINGULAR_CC%}',
                DIRECTORY_SEPARATOR . 'validators' . DIRECTORY_SEPARATOR .
                'Board' . DIRECTORY_SEPARATOR . 'BoardUpdateForm.php' => 'Board',
            ],
            [
                'WebController.txt' => '{%SINGULAR_CC%},{%PLURAL_CC%},{%PLURAL_LC%},{%PLURAL_UC%}',
                DIRECTORY_SEPARATOR . 'web-controllers' . DIRECTORY_SEPARATOR .
                'BoardsController.php' => 'Board,Boards,boards,BOARDS',
            ],
            [
                'WebRoutes.txt' => '{%SINGULAR_CC%},{%PLURAL_CC%}',
                DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'BoardWebRoutes.php' => 'Board,Boards',
            ],
        ]);
    }

    /**
     * Test command for make web controller.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testMakeJsonApiResource(): void
    {
        $this->checkOutputs(MakeCommand::ITEM_JSON_API_RESOURCE, 'Board', 'Boards', [
            [
                'Model.txt' => '{%SINGULAR_CC%},{%SINGULAR_LC%},{%SINGULAR_UC%},{%PLURAL_LC%},{%PLURAL_UC%}',
                DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Board.php' => 'Board,board,BOARD,boards,BOARDS',
            ],
            [
                'Migration.txt' => '{%SINGULAR_CC%},{%PLURAL_CC%}',
                DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . 'BoardsMigration.php' => 'Board,Boards',
            ],
            [
                'Seed.txt' => '{%SINGULAR_CC%},{%PLURAL_CC%}',
                DIRECTORY_SEPARATOR . 'seeds' . DIRECTORY_SEPARATOR . 'BoardsSeed.php' => 'Board,Boards',
            ],
            [
                'Schema.txt' => '{%SINGULAR_CC%},{%PLURAL_LC%}',
                DIRECTORY_SEPARATOR . 'schemas' . DIRECTORY_SEPARATOR . 'BoardSchema.php' => 'Board,boards',
            ],
            [
                'Api.txt' => '{%SINGULAR_CC%},{%PLURAL_CC%},{%SINGULAR_UC%},{%PLURAL_UC%}',
                DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'BoardsApi.php' => 'Board,Boards,BOARD,BOARDS',
            ],
            [
                'QueryRulesOnRead.txt' => '{%SINGULAR_CC%},{%PLURAL_CC%}',
                DIRECTORY_SEPARATOR . 'validators' . DIRECTORY_SEPARATOR .
                'Board' . DIRECTORY_SEPARATOR . 'BoardsReadQuery.php' => 'Board,Boards',
            ],
            [
                'ApiAuthorization.txt' =>
                    '{%SINGULAR_CC%},{%PLURAL_CC%},{%SINGULAR_UC%},{%PLURAL_UC%},{%SINGULAR_LC%}',
                DIRECTORY_SEPARATOR . 'policies' . DIRECTORY_SEPARATOR .
                'BoardRules.php' => 'Board,Boards,BOARD,BOARDS,board',
            ],
            [
                'ValidationRules.txt' => '{%SINGULAR_CC%},{%PLURAL_LC%},{%SINGULAR_LC%}',
                DIRECTORY_SEPARATOR . 'rules' . DIRECTORY_SEPARATOR .
                'Board' . DIRECTORY_SEPARATOR . 'BoardRules.php' => 'Board,boards,board',
            ],
            [
                'JsonRulesOnCreate.txt' => '{%SINGULAR_CC%},{%SINGULAR_LC%}',
                DIRECTORY_SEPARATOR . 'validators' . DIRECTORY_SEPARATOR .
                'Board' . DIRECTORY_SEPARATOR . 'BoardCreateJson.php' => 'Board,board',
            ],
            [
                'JsonRulesOnUpdate.txt' => '{%SINGULAR_CC%},{%SINGULAR_LC%}',
                DIRECTORY_SEPARATOR . 'validators' . DIRECTORY_SEPARATOR .
                'Board' . DIRECTORY_SEPARATOR . 'BoardUpdateJson.php' => 'Board,board',
            ],
            [
                'JsonController.txt' => '{%SINGULAR_CC%},{%PLURAL_CC%}',
                DIRECTORY_SEPARATOR . 'json-controllers' . DIRECTORY_SEPARATOR .
                'BoardsController.php' => 'Board,Boards',
            ],
            [
                'JsonRoutes.txt' => '{%SINGULAR_CC%},{%PLURAL_CC%}',
                DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'BoardApiRoutes.php' => 'Board,Boards',
            ],
        ]);
    }

    /**
     * Test command for make model seed.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testMakeWebResource(): void
    {
        $this->checkOutputs(MakeCommand::ITEM_WEB_RESOURCE, 'Board', 'Boards', [
            [
                'Model.txt' => '{%SINGULAR_CC%},{%SINGULAR_LC%},{%SINGULAR_UC%},{%PLURAL_LC%},{%PLURAL_UC%}',
                DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Board.php' => 'Board,board,BOARD,boards,BOARDS',
            ],
            [
                'Migration.txt' => '{%SINGULAR_CC%},{%PLURAL_CC%}',
                DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . 'BoardsMigration.php' => 'Board,Boards',
            ],
            [
                'Seed.txt' => '{%SINGULAR_CC%},{%PLURAL_CC%}',
                DIRECTORY_SEPARATOR . 'seeds' . DIRECTORY_SEPARATOR . 'BoardsSeed.php' => 'Board,Boards',
            ],
            [
                'Schema.txt' => '{%SINGULAR_CC%},{%PLURAL_LC%}',
                DIRECTORY_SEPARATOR . 'schemas' . DIRECTORY_SEPARATOR . 'BoardSchema.php' => 'Board,boards',
            ],
            [
                'Api.txt' => '{%SINGULAR_CC%},{%PLURAL_CC%},{%SINGULAR_UC%},{%PLURAL_UC%}',
                DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'BoardsApi.php' => 'Board,Boards,BOARD,BOARDS',
            ],
            [
                'QueryRulesOnRead.txt' => '{%SINGULAR_CC%},{%PLURAL_CC%}',
                DIRECTORY_SEPARATOR . 'validators' . DIRECTORY_SEPARATOR .
                'Board' . DIRECTORY_SEPARATOR . 'BoardsReadQuery.php' => 'Board,Boards',
            ],
            [
                'ApiAuthorization.txt' =>
                    '{%SINGULAR_CC%},{%PLURAL_CC%},{%SINGULAR_UC%},{%PLURAL_UC%},{%SINGULAR_LC%}',
                DIRECTORY_SEPARATOR . 'policies' . DIRECTORY_SEPARATOR .
                'BoardRules.php' => 'Board,Boards,BOARD,BOARDS,board',
            ],
            [
                'ValidationRules.txt' => '{%SINGULAR_CC%},{%PLURAL_LC%},{%SINGULAR_LC%}',
                DIRECTORY_SEPARATOR . 'rules' . DIRECTORY_SEPARATOR .
                'Board' . DIRECTORY_SEPARATOR . 'BoardRules.php' => 'Board,boards,board',
            ],
            [
                'WebRulesOnCreate.txt' => '{%SINGULAR_CC%}',
                DIRECTORY_SEPARATOR . 'validators' . DIRECTORY_SEPARATOR .
                'Board' . DIRECTORY_SEPARATOR . 'BoardCreateForm.php' => 'Board',
            ],
            [
                'WebRulesOnUpdate.txt' => '{%SINGULAR_CC%}',
                DIRECTORY_SEPARATOR . 'validators' . DIRECTORY_SEPARATOR .
                'Board' . DIRECTORY_SEPARATOR . 'BoardUpdateForm.php' => 'Board',
            ],
            [
                'WebController.txt' => '{%SINGULAR_CC%},{%PLURAL_CC%},{%PLURAL_LC%},{%PLURAL_UC%}',
                DIRECTORY_SEPARATOR . 'web-controllers' . DIRECTORY_SEPARATOR .
                'BoardsController.php' => 'Board,Boards,boards,BOARDS',
            ],
            [
                'WebRoutes.txt' => '{%SINGULAR_CC%},{%PLURAL_CC%}',
                DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'BoardWebRoutes.php' => 'Board,Boards',
            ],
        ]);
    }

    /**
     * Test command for make model migration.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testMakeDataResource(): void
    {
        $this->checkOutputs(MakeCommand::ITEM_DATA_RESOURCE, 'Board', 'Boards', [
            [
                'Model.txt' => '{%SINGULAR_CC%},{%SINGULAR_LC%},{%SINGULAR_UC%},{%PLURAL_LC%},{%PLURAL_UC%}',
                DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Board.php' => 'Board,board,BOARD,boards,BOARDS',
            ],
            [
                'Migration.txt' => '{%SINGULAR_CC%},{%PLURAL_CC%}',
                DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . 'BoardsMigration.php' => 'Board,Boards',
            ],
            [
                'Seed.txt' => '{%SINGULAR_CC%},{%PLURAL_CC%}',
                DIRECTORY_SEPARATOR . 'seeds' . DIRECTORY_SEPARATOR . 'BoardsSeed.php' => 'Board,Boards',
            ],
        ]);
    }

    /**
     * Test command descriptions.
     */
    public function testCommandDescriptions(): void
    {
        $this->assertNotEmpty(MakeCommand::getName());
        $this->assertNotEmpty(MakeCommand::getHelp());
        $this->assertNotEmpty(MakeCommand::getDescription());
        $this->assertNotEmpty(MakeCommand::getArguments());
        $this->assertEmpty(MakeCommand::getOptions());
    }

    /**
     * Test invalid class name.
     */
    public function testInvalidClassName1(): void
    {
        $inOut = $this->createInOutMock([
            MakeCommand::ARG_ITEM => MakeCommand::ITEM_FULL_RESOURCE,
            MakeCommand::ARG_SINGULAR => 'Invalid Class Name',
            MakeCommand::ARG_PLURAL => 'Boards',
        ]);

        $this->expectException(InvalidArgumentException::class);

        MakeCommand::execute($this->createContainerWithSettings(), $inOut);
    }

    /**
     * Test invalid class name.
     */
    public function testInvalidClassName2(): void
    {
        $inOut = $this->createInOutMock([
            MakeCommand::ARG_ITEM => MakeCommand::ITEM_FULL_RESOURCE,
            MakeCommand::ARG_SINGULAR => 'Board',
            MakeCommand::ARG_PLURAL => 'Invalid Class Name',
        ]);


        $this->expectException(InvalidArgumentException::class);
        MakeCommand::execute($this->createContainerWithSettings(), $inOut);
    }

    /**
     * Test command called with invalid item parameter.
     */
    public function testInvalidItem(): void
    {
        $inOut = $this->createInOutMock(
            [
                MakeCommand::ARG_ITEM => 'non_existing_item',
                MakeCommand::ARG_SINGULAR => 'Board',
                MakeCommand::ARG_PLURAL => 'Boards',
            ],
            [],
            true
        );

        MakeCommand::execute($this->createContainerWithSettings(), $inOut);

        // Mockery will do checks when the test finishes
        $this->assertTrue(true);
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testItShouldFailIfRootDirectoryDoNotExist(): void
    {
        $this->fileSystemMock->shouldReceive('exists')->zeroOrMoreTimes()->with(
            DIRECTORY_SEPARATOR . 'models'
        )->andReturnTrue();
        $this->fileSystemMock->shouldReceive('exists')->zeroOrMoreTimes()->with(
            DIRECTORY_SEPARATOR . 'migrations'
        )->andReturnTrue();

        // this one will trigger the error
        $this->fileSystemMock->shouldReceive('exists')->zeroOrMoreTimes()->with(
            DIRECTORY_SEPARATOR . 'seeds'
        )->andReturnFalse();

        $this->expectException(InvalidArgumentException::class);

        $this->prepareDataForDataResourceToEmulateFileSystemIssuesAndRunTheCommand();
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testItShouldFailIfOneOfTheFilesAlreadyExists(): void
    {
        $this->fileSystemMock->shouldReceive('exists')->zeroOrMoreTimes()->with(
            DIRECTORY_SEPARATOR . 'models'
        )->andReturnTrue();
        $this->fileSystemMock->shouldReceive('exists')->zeroOrMoreTimes()->with(
            DIRECTORY_SEPARATOR . 'migrations'
        )->andReturnTrue();
        $this->fileSystemMock->shouldReceive('exists')->zeroOrMoreTimes()->with(
            DIRECTORY_SEPARATOR . 'seeds'
        )->andReturnTrue();

        // this one will trigger the error
        $this->fileSystemMock->shouldReceive('exists')
            ->once()->with(DIRECTORY_SEPARATOR . 'seeds' . DIRECTORY_SEPARATOR . 'BoardsSeed.php')->andReturnTrue();

        $this->expectException(InvalidArgumentException::class);

        $this->prepareDataForDataResourceToEmulateFileSystemIssuesAndRunTheCommand();
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testItShouldFailIfOutputFolderIsNotWritable(): void
    {
        $this->fileSystemMock->shouldReceive('exists')->zeroOrMoreTimes()->with(
            DIRECTORY_SEPARATOR . 'models'
        )->andReturnTrue();
        $this->fileSystemMock->shouldReceive('exists')->zeroOrMoreTimes()->with(
            DIRECTORY_SEPARATOR . 'migrations'
        )->andReturnTrue();
        $this->fileSystemMock->shouldReceive('exists')->zeroOrMoreTimes()
            ->with(DIRECTORY_SEPARATOR . 'seeds')->andReturnTrue();

        // this one will trigger the error
        $this->fileSystemMock->shouldReceive('isWritable')->once()->with(DIRECTORY_SEPARATOR . 'seeds')->andReturnFalse(
        );

        $this->expectException(InvalidArgumentException::class);

        $this->prepareDataForDataResourceToEmulateFileSystemIssuesAndRunTheCommand();
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testItShouldFailIfRootFolderIsNotWritable(): void
    {
        $this->fileSystemMock->shouldReceive('exists')->once()->with(DIRECTORY_SEPARATOR . 'migrations')->andReturnTrue(
        );

        // this one will trigger the error
        $this->fileSystemMock->shouldReceive('isWritable')
            ->once()->with(DIRECTORY_SEPARATOR . 'migrations')->andReturnFalse();

        $this->expectException(InvalidArgumentException::class);

        $this->prepareDataForDataResourceToEmulateFileSystemIssuesAndRunTheCommand();
    }

    /**
     * Run command test.
     * @param string $command
     * @param string $singular
     * @param string $plural
     * @param array $fileExpectations
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function checkOutputs(string $command, string $singular, string $plural, array $fileExpectations): void
    {
        $dataSettings = [
            DataSettingsInterface::KEY_MODELS_FOLDER => DIRECTORY_SEPARATOR . 'models',
            DataSettingsInterface::KEY_MIGRATIONS_FOLDER => DIRECTORY_SEPARATOR . 'migrations',
            DataSettingsInterface::KEY_SEEDS_FOLDER => DIRECTORY_SEPARATOR . 'seeds',
        ];
        $fluteSettings = [
            FluteSettingsInterface::KEY_SCHEMAS_FOLDER => DIRECTORY_SEPARATOR . 'schemas',
            FluteSettingsInterface::KEY_JSON_VALIDATION_RULES_FOLDER => DIRECTORY_SEPARATOR . 'rules',
            FluteSettingsInterface::KEY_JSON_VALIDATORS_FOLDER => DIRECTORY_SEPARATOR . 'validators',
            FluteSettingsInterface::KEY_JSON_CONTROLLERS_FOLDER => DIRECTORY_SEPARATOR . 'json-controllers',
            FluteSettingsInterface::KEY_WEB_CONTROLLERS_FOLDER => DIRECTORY_SEPARATOR . 'web-controllers',
            FluteSettingsInterface::KEY_ROUTES_FOLDER => DIRECTORY_SEPARATOR . 'routes',
            FluteSettingsInterface::KEY_API_FOLDER => DIRECTORY_SEPARATOR . 'api',
        ];
        $authSettings = [
            AuthorizationSettingsInterface::KEY_POLICIES_FOLDER => DIRECTORY_SEPARATOR . 'policies',
        ];

        $existingFolders = array_merge(
            array_values($dataSettings),
            array_values($fluteSettings),
            array_values($authSettings)
        );
        foreach ($existingFolders as $existingFolder) {
            $this->fileSystemMock->shouldReceive('exists')->zeroOrMoreTimes()->with($existingFolder)->andReturnTrue();
        }
        $this->fileSystemMock->shouldReceive('exists')->zeroOrMoreTimes()->withAnyArgs()->andReturnFalse();
        $this->fileSystemMock->shouldReceive('isWritable')->zeroOrMoreTimes()->withAnyArgs()->andReturnTrue();
        $this->fileSystemMock->shouldReceive('createFolder')->zeroOrMoreTimes()->withAnyArgs()->andReturnUndefined();

        $container = $this->createContainerWithSettings($dataSettings, $fluteSettings, $authSettings);
        $inOut = $this->createInOutMock([
            MakeCommand::ARG_ITEM => $command,
            MakeCommand::ARG_SINGULAR => $singular,
            MakeCommand::ARG_PLURAL => $plural,
        ]);

        foreach ($fileExpectations as $expectation) {
            $files = array_keys($expectation);
            $bodies = array_values($expectation);
            $this->fileSystemMock->shouldReceive('read')->once()
                ->with($this->getPathToResource($files[0]))->andReturn($bodies[1]);
            $this->fileSystemMock->shouldReceive('write')->once()
                ->with($files[1], $bodies[1])->andReturnUndefined();
        }

        MakeCommand::execute($container, $inOut);

        // Mockery will do checks when the test finishes
        $this->assertTrue(true);
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function prepareDataForDataResourceToEmulateFileSystemIssuesAndRunTheCommand(): void
    {
        $dataSettings = [
            DataSettingsInterface::KEY_MODELS_FOLDER => DIRECTORY_SEPARATOR . 'models',
            DataSettingsInterface::KEY_MIGRATIONS_FOLDER => DIRECTORY_SEPARATOR . 'migrations',
            DataSettingsInterface::KEY_SEEDS_FOLDER => DIRECTORY_SEPARATOR . 'seeds',
        ];

        $this->fileSystemMock->shouldReceive('exists')->zeroOrMoreTimes()->withAnyArgs()->andReturnFalse();
        $this->fileSystemMock->shouldReceive('isWritable')->zeroOrMoreTimes()->withAnyArgs()->andReturnTrue();
        $this->fileSystemMock->shouldReceive('createFolder')->zeroOrMoreTimes()->withAnyArgs()->andReturnUndefined();

        $container = $this->createContainerWithSettings($dataSettings, [], []);
        $inOut = $this->createInOutMock([
            MakeCommand::ARG_ITEM => MakeCommand::ITEM_DATA_RESOURCE,
            MakeCommand::ARG_SINGULAR => 'Board',
            MakeCommand::ARG_PLURAL => 'Boards',
        ]);

        $fileExpectations = [
            [
                'Model.txt' => 'content does not matter for this test',
                DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR .
                'Board.php' => 'content does not matter for this test',
            ],
            [
                'Migration.txt' => 'content does not matter for this test',
                DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR .
                'BoardsMigration.php' => 'content does not matter for this test',
            ],
            [
                'Seed.txt' => 'content does not matter for this test',
                DIRECTORY_SEPARATOR . 'seeds' . DIRECTORY_SEPARATOR .
                'BoardsSeed.php' => 'content does not matter for this test',
            ],
        ];

        foreach ($fileExpectations as $expectation) {
            $files = array_keys($expectation);
            $bodies = array_values($expectation);
            $this->fileSystemMock->shouldReceive('read')->once()
                ->with($this->getPathToResource($files[0]))->andReturn($bodies[1]);
        }

        MakeCommand::execute($container, $inOut);

        // Mockery will do checks when the test finishes
        $this->assertTrue(true);
    }

    /**
     * @param array $dataSettings
     * @param array $fluteSettings
     * @param array $authSettings
     * @return Container
     */
    private function createContainerWithSettings(
        array $dataSettings = [],
        array $fluteSettings = [],
        array $authSettings = []
    ): Container {
        $container = new Container();

        /** @var Mock $providerMock */
        $container[SettingsProviderInterface::class] = $providerMock = Mockery::mock(SettingsProviderInterface::class);
        $providerMock->shouldReceive('get')
            ->zeroOrMoreTimes()->with(DataSettingsInterface::class)->andReturn($dataSettings);
        $providerMock->shouldReceive('get')
            ->zeroOrMoreTimes()->with(FluteSettingsInterface::class)->andReturn($fluteSettings);
        $providerMock->shouldReceive('get')
            ->zeroOrMoreTimes()->with(AuthorizationSettingsInterface::class)->andReturn($authSettings);

        $container[FileSystemInterface::class] = $this->fileSystemMock;

        return $container;
    }

    /**
     * @param array $arguments
     * @param array $options
     * @param bool $expectErrors
     * @return IoInterface
     */
    private function createInOutMock(array $arguments, array $options = [], bool $expectErrors = false): IoInterface
    {
        /** @var Mock $mock */
        $mock = Mockery::mock(IoInterface::class);
        $mock->shouldReceive('getArguments')->zeroOrMoreTimes()->withNoArgs()->andReturn($arguments);
        $mock->shouldReceive('getOptions')->zeroOrMoreTimes()->withNoArgs()->andReturn($options);
        if ($expectErrors === true) {
            $mock->shouldReceive('writeError')->zeroOrMoreTimes()->withAnyArgs()->andReturnSelf();
        }

        /** @var IoInterface $mock */

        return $mock;
    }

    /**
     * @param string $fileName
     * @return string
     */
    private function getPathToResource(string $fileName): string
    {
        $root = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..']);
        $filePath = realpath(implode(DIRECTORY_SEPARATOR, [$root, 'src', 'Commands', 'MakeCommand.php']));
        $folderPath = substr($filePath, 0, -16);

        return implode(DIRECTORY_SEPARATOR, [$folderPath, '..', '..', 'res', 'CodeTemplates', $fileName]);
    }
}
