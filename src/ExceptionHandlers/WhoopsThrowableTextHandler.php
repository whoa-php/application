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

namespace Whoa\Application\ExceptionHandlers;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Whoa\Contracts\Http\ThrowableResponseInterface;
use Psr\Container\ContainerInterface;
use Throwable;
use Whoops\Handler\PlainTextHandler;
use Whoops\Run;

/**
 * @package Whoa\Application
 */
class WhoopsThrowableTextHandler extends BaseThrowableHandler
{
    /**
     * @inheritdoc
     * @param Throwable $throwable
     * @param ContainerInterface $container
     * @return ThrowableResponseInterface
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function createResponse(Throwable $throwable, ContainerInterface $container): ThrowableResponseInterface
    {
        $message = 'Internal Server Error';

        $this->logException($throwable, $container, $message);

        list($isDebug) = $this->getSettings($container);

        if ($isDebug === true) {
            $run = new Run();

            // If these two options are not used it would work fine with PHP Unit and XDebug,
            // however it produces output to console under PhpDbg. So we need a couple of
            // tweaks to make it work predictably in both environments.
            //
            // this one forbids Whoops spilling output to console
            $run->writeToOutput(false);
            // by default after sending error to output Whoops stops execution
            // as we want just generated output `string` we instruct not to halt
            $run->allowQuit(false);

            $handler = new PlainTextHandler();
            $handler->setException($throwable);
            $run->appendHandler($handler);

            $message = $run->handleException($throwable);
        }

        $status = $throwable->getCode() > 0 ? $throwable->getCode() : static::DEFAULT_HTTP_ERROR_CODE;
        return $this->createThrowableTextResponse($throwable, $message, $status);
    }
}
