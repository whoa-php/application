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

namespace Whoa\Application\CoreSettings;

use FastRoute\DataGenerator\GroupCountBased as GroupCountBasedGenerator;
use Generator;
use Whoa\Common\Reflection\CheckCallableTrait;
use Whoa\Common\Reflection\ClassIsTrait;
use Whoa\Contracts\Application\ContainerConfiguratorInterface;
use Whoa\Contracts\Application\MiddlewareInterface;
use Whoa\Contracts\Application\RoutesConfiguratorInterface;
use Whoa\Contracts\Container\ContainerInterface;
use Whoa\Contracts\Provider\ProvidesContainerConfiguratorsInterface;
use Whoa\Contracts\Provider\ProvidesMiddlewareInterface;
use Whoa\Contracts\Provider\ProvidesRouteConfiguratorsInterface;
use Whoa\Contracts\Routing\GroupInterface;
use Whoa\Contracts\Routing\RouterInterface;
use Whoa\Core\Application\BaseCoreData;
use Whoa\Core\Routing\Dispatcher\GroupCountBased as GroupCountBasedDispatcher;
use Whoa\Core\Routing\Group;
use Whoa\Core\Routing\Router;
use ReflectionException;

use function assert;
use function iterator_to_array;

/**
 * @package Whoa\Application
 */
class CoreData extends BaseCoreData
{
    use CheckCallableTrait;
    use ClassIsTrait;

    /**
     * @var string
     */
    private string $routesPath;

    /**
     * @var string
     */
    private string $configuratorsPath;

    /**
     * @var string[]
     */
    private array $providerClasses;

    /**
     * @param string $routesPath
     * @param string $configuratorsPath
     * @param string[] $providerClasses
     */
    public function __construct(string $routesPath, string $configuratorsPath, array $providerClasses)
    {
        $this->routesPath = $routesPath;
        $this->configuratorsPath = $configuratorsPath;
        $this->providerClasses = $providerClasses;
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function get(): array
    {
        list ($generatorClass, $dispatcherClass) = $this->getGeneratorAndDispatcherClasses();

        $routesData = $this
            ->createRouter($generatorClass, $dispatcherClass)
            ->getCachedRoutes($this->addRoutes($this->createGroup()));

        $globalConfigurators = iterator_to_array($this->getGlobalContainerConfigurators(), false);
        $globalMiddleware = iterator_to_array($this->getGlobalMiddleWareHandlers(), false);

        return [
            static::KEY_ROUTER_PARAMS => [
                static::KEY_ROUTER_PARAMS__GENERATOR => $generatorClass,
                static::KEY_ROUTER_PARAMS__DISPATCHER => $dispatcherClass,
            ],
            static::KEY_ROUTES_DATA => $routesData,
            static::KEY_GLOBAL_CONTAINER_CONFIGURATORS => $globalConfigurators,
            static::KEY_GLOBAL_MIDDLEWARE => $globalMiddleware,
        ];
    }

    /**
     * @return array
     */
    protected function getGeneratorAndDispatcherClasses(): array
    {
        return [GroupCountBasedGenerator::class, GroupCountBasedDispatcher::class];
    }

    /**
     * @return Generator
     * @throws ReflectionException
     */
    protected function getGlobalContainerConfigurators(): Generator
    {
        // configurators from providers first
        $interfaceName = ProvidesContainerConfiguratorsInterface::class;
        foreach ($this->selectClassImplements($this->getProviderClasses(), $interfaceName) as $providerClass) {
            /** @var ProvidesContainerConfiguratorsInterface $providerClass */
            foreach ($providerClass::getContainerConfigurators() as $configuratorClass) {
                $configurator = [$configuratorClass, ContainerConfiguratorInterface::CONTAINER_METHOD_NAME];
                assert($this->isValidContainerConfigurator($configurator) === true);
                yield $configurator;
            }
        }

        // then configurators from the application so they can override providers
        $interfaceName = ContainerConfiguratorInterface::class;
        foreach ($this->selectClasses($this->getConfiguratorsPath(), $interfaceName) as $configuratorClass) {
            $configurator = [$configuratorClass, ContainerConfiguratorInterface::CONTAINER_METHOD_NAME];
            assert($this->isValidContainerConfigurator($configurator) === true);
            yield $configurator;
        }
    }

    /**
     * @param GroupInterface $group
     * @return GroupInterface
     * @throws ReflectionException
     */
    protected function addRoutes(GroupInterface $group): GroupInterface
    {
        $interfaceName = RoutesConfiguratorInterface::class;
        foreach ($this->selectClasses($this->getRoutesPath(), $interfaceName) as $routesConfClass) {
            /** @var RoutesConfiguratorInterface $routesConfClass */
            $routesConfClass::configureRoutes($group);
        }

        $interfaceName = ProvidesRouteConfiguratorsInterface::class;
        foreach ($this->selectClassImplements($this->getProviderClasses(), $interfaceName) as $providerClass) {
            /** @var ProvidesRouteConfiguratorsInterface $providerClass */
            foreach ($providerClass::getRouteConfigurators() as $routesConfClass) {
                /** @var RoutesConfiguratorInterface $routesConfClass */
                $routesConfClass::configureRoutes($group);
            }
        }

        return $group;
    }

    /**
     * @return Generator
     * @throws ReflectionException
     */
    protected function getGlobalMiddleWareHandlers(): Generator
    {
        // select global middleware from routes
        foreach ($this->selectClasses($this->getRoutesPath(), RoutesConfiguratorInterface::class) as $selectClass) {
            /** @var RoutesConfiguratorInterface $selectClass */
            foreach ($selectClass::getMiddleware() as $middlewareClass) {
                yield [$middlewareClass, MiddlewareInterface::MIDDLEWARE_METHOD_NAME];
            }
        }

        // select global middleware from providers
        $interfaceName = ProvidesMiddlewareInterface::class;
        foreach ($this->selectClassImplements($this->getProviderClasses(), $interfaceName) as $providerClass) {
            /** @var ProvidesMiddlewareInterface $providerClass */
            foreach ($providerClass::getMiddleware() as $middlewareClass) {
                yield [$middlewareClass, MiddlewareInterface::MIDDLEWARE_METHOD_NAME];
            }
        }
    }

    /**
     * @return GroupInterface
     */
    protected function createGroup(): GroupInterface
    {
        return new Group();
    }

    /**
     * @param string $generatorClass
     * @param string $dispatcherClass
     * @return RouterInterface
     */
    protected function createRouter(string $generatorClass, string $dispatcherClass): RouterInterface
    {
        return new Router($generatorClass, $dispatcherClass);
    }

    /**
     * @return string
     */
    protected function getConfiguratorsPath(): string
    {
        return $this->configuratorsPath;
    }

    /**
     * @return string[]
     */
    protected function getProviderClasses(): array
    {
        return $this->providerClasses;
    }

    /**
     * @return string
     */
    protected function getRoutesPath(): string
    {
        return $this->routesPath;
    }

    /**
     * @param string|array|callable $mightBeConfigurator
     * @return bool
     * @throws ReflectionException
     */
    private function isValidContainerConfigurator($mightBeConfigurator): bool
    {
        return $this->checkPublicStaticCallable($mightBeConfigurator, [ContainerInterface::class]);
    }
}
