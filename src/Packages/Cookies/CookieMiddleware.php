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

namespace Whoa\Application\Packages\Cookies;

use Closure;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Whoa\Application\Contracts\Cookie\CookieFunctionsInterface;
use Whoa\Contracts\Application\MiddlewareInterface;
use Whoa\Contracts\Cookies\CookieInterface;
use Whoa\Contracts\Cookies\CookieJarInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function call_user_func;

/**
 * @package Whoa\Application
 */
class CookieMiddleware implements MiddlewareInterface
{
    /** Middleware handler */
    public const CALLABLE_HANDLER = [self::class, self::MIDDLEWARE_METHOD_NAME];

    /**
     * @inheritdoc
     * @param ServerRequestInterface $request
     * @param Closure $next
     * @param ContainerInterface $container
     * @return ResponseInterface
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public static function handle(
        ServerRequestInterface $request,
        Closure $next,
        ContainerInterface $container
    ): ResponseInterface {
        /** @var ResponseInterface $response */
        $response = $next($request);

        if ($container->has(CookieJarInterface::class) === true) {
            /** @var CookieJarInterface $cookieJar */
            $cookieJar = $container->get(CookieJarInterface::class);
            /** @var CookieFunctionsInterface $cookieFunctions */
            $cookieFunctions = $container->get(CookieFunctionsInterface::class);
            foreach ($cookieJar->getAll() as $cookie) {
                /** @var CookieInterface $cookie */

                // The reason why methods for setting cookies are called with call_user_func and their names
                // is that calling `setcookie` is not possible during testing. It can't add any headers
                // in a console app which produces some output.
                //
                // Using functions by names allows to replace them with test mocks and check input values.
                //
                // Notice constants are used with `static::`. Their values can and will be replaced in tests.
                $setCookieFunction = $cookie->isNotRaw() === true ?
                    $cookieFunctions->getWriteCookieCallable() : $cookieFunctions->getWriteRawCookieCallable();

                call_user_func(
                    $setCookieFunction,
                    $cookie->getName(),
                    $cookie->getValue(),
                    $cookie->getExpiresAtUnixTime(),
                    $cookie->getPath(),
                    $cookie->getDomain(),
                    $cookie->isSendOnlyOverSecureConnection(),
                    $cookie->isAccessibleOnlyThroughHttp()
                );
            }
        }

        return $response;
    }
}
