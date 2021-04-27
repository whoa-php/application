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

namespace Whoa\Tests\Application\Authorization;

use Whoa\Application\Authorization\AuthorizationRulesTrait;
use Whoa\Application\Authorization\ContextProperties;
use Whoa\Application\Authorization\RequestProperties;
use Whoa\Application\Exceptions\AuthorizationException;
use Whoa\Application\Packages\Authorization\AuthorizationContainerConfigurator;
use Whoa\Application\Packages\Authorization\AuthorizationSettings as C;
use Whoa\Auth\Authorization\PolicyEnforcement\Request;
use Whoa\Auth\Authorization\PolicyInformation\Context;
use Whoa\Container\Container;
use Whoa\Contracts\Authentication\AccountInterface;
use Whoa\Contracts\Authentication\AccountManagerInterface;
use Whoa\Contracts\Authorization\AuthorizationManagerInterface;
use Whoa\Contracts\Exceptions\AuthorizationExceptionInterface;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Whoa\Tests\Application\Data\Authorization\NotificationRules;
use Whoa\Tests\Application\Packages\Authorization\AuthorizationPackageTest;
use Whoa\Tests\Application\TestCase;
use Mockery;
use Mockery\Mock;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionException;

/**
 * @package Whoa\Tests\Application
 */
class AuthorizationManagerTest extends TestCase
{
    use AuthorizationRulesTrait;

    /**
     * Test wrappers for working with context.
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testTraitWrappers(): void
    {
        $actionName = 'some_action';
        $resType = 'some_type';
        $resIdentity = 'some_identity';
        $resAttributes = ['attribute_name' => 'value'];
        $resRelationships = ['relationships_name1' => 'value1', 'relationships_name2' => ['value2', 'value3']];
        $container = new Container();

        /** @var Mock $managerMock */
        $container[AccountManagerInterface::class] = $managerMock = Mockery::mock(AccountManagerInterface::class);
        $managerMock->shouldReceive('getAccount')->zeroOrMoreTimes()->withNoArgs()
            ->andReturn($curAccount = Mockery::mock(AccountInterface::class));

        $ctxDefinitions = [
            ContextProperties::CTX_CONTAINER => $container,
        ];
        $reqProperties = [
            RequestProperties::REQ_ACTION => $actionName,
            RequestProperties::REQ_RESOURCE_TYPE => $resType,
            RequestProperties::REQ_RESOURCE_IDENTITY => $resIdentity,

            RequestProperties::REQ_RESOURCE_ATTRIBUTES => $resAttributes,
            RequestProperties::REQ_RESOURCE_RELATIONSHIPS => $resRelationships,
        ];

        $context = new Context(new Request($reqProperties), $ctxDefinitions);

        $this->assertEquals($actionName, $this->reqGetAction($context));
        $this->assertEquals($resType, $this->reqGetResourceType($context));
        $this->assertEquals($resIdentity, $this->reqGetResourceIdentity($context));
        $this->assertEquals($resAttributes, $this->reqGetResourceAttributes($context));
        $this->assertEquals($resRelationships, $this->reqGetResourceRelationships($context));
        $this->assertEquals($container, $this->ctxGetContainer($context));
        $this->assertEquals($curAccount, $this->ctxGetCurrentAccount($context));
    }

    /**
     * Test authorization.
     * @return void
     * @throws AuthorizationExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testAuthorize(): void
    {
        $container = new Container();

        /** @var Mock $provider */
        $appSettings = [];
        $container[SettingsProviderInterface::class] = $provider = Mockery::mock(SettingsProviderInterface::class);
        $container[LoggerInterface::class] = new NullLogger();
        $provider->shouldReceive('get')->once()->with(C::class)
            ->andReturn(AuthorizationPackageTest::getAuthorizationSettings()->get($appSettings));

        AuthorizationContainerConfigurator::configureContainer($container);

        /** @var AuthorizationManagerInterface $manager */
        $this->assertNotNull($manager = $container->get(AuthorizationManagerInterface::class));

        $gotException = false;
        $actionName = 'some_non_existing_action';
        try {
            $manager->authorize($actionName);
        } catch (AuthorizationException $exception) {
            $gotException = true;
            $this->assertEquals($actionName, $exception->getAction());
            $this->assertEmpty($exception->getResourceType());
            $this->assertEmpty($exception->getResourceIdentity());
            $this->assertEmpty($exception->getExtraParameters());
        }
        $this->assertTrue($gotException);

        // this one pass check and not throw auth exception
        $manager->authorize(NotificationRules::ACTION_CAN_SEND_PERSONAL_EMAILS);
    }
}
