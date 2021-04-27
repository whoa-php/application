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

namespace Whoa\Application\Packages\Application;

use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Whoa\Application\CoreSettings\CoreData;
use Whoa\Application\Settings\CacheSettingsProvider;
use Whoa\Application\Settings\FileSettingsProvider;
use Whoa\Application\Settings\InstanceSettingsProvider;
use Whoa\Common\Reflection\ClassIsTrait;
use Whoa\Container\Container;
use Whoa\Contracts\Application\ApplicationConfigurationInterface as A;
use Whoa\Contracts\Application\CacheSettingsProviderInterface;
use Whoa\Contracts\Container\ContainerInterface as WhoaContainerInterface;
use Whoa\Contracts\Core\SapiInterface;
use Whoa\Contracts\Provider\ProvidesSettingsInterface;
use Whoa\Contracts\Routing\RouterInterface;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Whoa\Core\Application\Sapi;
use ReflectionException;

use function assert;
use function call_user_func;
use function count;
use function is_array;
use function is_callable;
use function is_null;
use function is_string;
use function iterator_to_array;
use function reset;

/**
 * @package Whoa\Application
 */
class Application extends \Whoa\Core\Application\Application
{
    use ClassIsTrait;

    /**
     * @var string
     */
    private string $settingsPath;

    /**
     * @var callable|string|null
     */
    private $settingCacheMethod;

    /**
     * @var CacheSettingsProviderInterface|null
     */
    private ?CacheSettingsProviderInterface $cacheSettingsProvider = null;

    /**
     * @param string $settingsPath
     * @param string|array|callable|null $settingCacheMethod
     * @param SapiInterface|null $sapi
     */
    public function __construct(string $settingsPath, $settingCacheMethod = null, SapiInterface $sapi = null)
    {
        // The reason we do not use `callable` for the input parameter is that at the moment
        // of calling the callable might not exist. Therefore, when created it will pass
        // `is_callable` check and will be used for getting the cached data.
        assert(is_null($settingCacheMethod) || is_string($settingCacheMethod) || is_array($settingCacheMethod));

        $this->settingsPath = $settingsPath;
        $this->settingCacheMethod = $settingCacheMethod;

        $this->setSapi($sapi ?? new Sapi(new SapiEmitter()));
    }

    /**
     * @inheritdoc
     * @throws ReflectionException
     */
    protected function getCoreData(): array
    {
        return $this->getCacheSettingsProvider()->getCoreData();
    }

    /**
     * Get container from application. If `method` and `path` are specified the container will be configured
     * not only with global container configurators but with route's one as well.
     * @param string|null $method
     * @param string|null $path
     * @return WhoaContainerInterface
     * @throws ReflectionException
     */
    public function createContainer(string $method = null, string $path = null): WhoaContainerInterface
    {
        $container = $this->createContainerInstance();

        $routeConfigurators = [];
        $coreData = $this->getCoreData();
        if (empty($method) === false && empty($path) === false) {
            [, , , , , $routeConfigurators] = $this->initRouter($coreData)->match($method, $path);
        }

        // configure container
        assert(!empty($coreData));
        $globalConfigurators = CoreData::getGlobalConfiguratorsFromData($coreData);
        $this->configureContainer($container, $globalConfigurators, $routeConfigurators);

        return $container;
    }

    /**
     * @return WhoaContainerInterface
     * @throws ReflectionException
     */
    protected function createContainerInstance(): WhoaContainerInterface
    {
        $container = new Container();

        $settingsProvider = $this->getCacheSettingsProvider();
        $container->offsetSet(SettingsProviderInterface::class, $settingsProvider);
        $container->offsetSet(CacheSettingsProviderInterface::class, $settingsProvider);
        $container->offsetSet(RouterInterface::class, $this->getRouter());

        return $container;
    }

    /**
     * @return string
     */
    protected function getSettingsPath(): string
    {
        return $this->settingsPath;
    }

    /**
     * @return callable|string|null
     */
    protected function getSettingCacheMethod()
    {
        return $this->settingCacheMethod;
    }

    /**
     * @return CacheSettingsProviderInterface
     * @throws ReflectionException
     */
    private function getCacheSettingsProvider(): CacheSettingsProviderInterface
    {
        if ($this->cacheSettingsProvider === null) {
            $provider = new CacheSettingsProvider();

            if (is_callable($method = $this->getSettingCacheMethod()) === true) {
                $data = call_user_func($method);
                $provider->unserialize($data);
            } else {
                $appConfig = $this->createApplicationConfiguration();
                $appData = $appConfig->get();
                $coreData = new CoreData(
                    $appData[A::KEY_ROUTES_PATH],
                    $appData[A::KEY_CONTAINER_CONFIGURATORS_PATH],
                    $appData[A::KEY_PROVIDER_CLASSES]
                );

                $provider->setInstanceSettings($appConfig, $coreData, $this->createInstanceSettingsProvider($appData));
            }

            $this->cacheSettingsProvider = $provider;
        }

        return $this->cacheSettingsProvider;
    }

    /**
     * @return A
     * @throws ReflectionException
     */
    private function createApplicationConfiguration(): A
    {
        $classes = iterator_to_array(static::selectClasses($this->getSettingsPath(), A::class));
        assert(
            count($classes) > 0,
            'Settings path must contain a class implementing ApplicationConfigurationInterface.'
        );
        assert(
            count($classes) === 1,
            'Settings path must contain only one class implementing ApplicationConfigurationInterface.'
        );
        $class = reset($classes);
        $instance = new $class();
        assert($instance instanceof A);

        return $instance;
    }

    /**
     * @param array $applicationData
     * @return InstanceSettingsProvider
     * @throws ReflectionException
     */
    private function createInstanceSettingsProvider(array $applicationData): InstanceSettingsProvider
    {
        // Load all settings from path specified
        $provider = (new FileSettingsProvider($applicationData))->load($this->getSettingsPath());

        // Application settings have a list of providers which might have additional settings to load
        $providerClasses = $applicationData[A::KEY_PROVIDER_CLASSES];
        $selectedClasses = $this->selectClassImplements($providerClasses, ProvidesSettingsInterface::class);
        foreach ($selectedClasses as $providerClass) {
            /** @var ProvidesSettingsInterface $providerClass */
            foreach ($providerClass::getSettings() as $setting) {
                $provider->register($setting);
            }
        }

        return $provider;
    }
}
