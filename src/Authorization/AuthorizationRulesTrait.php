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

namespace Whoa\Application\Authorization;

use Whoa\Auth\Contracts\Authorization\PolicyInformation\ContextInterface;
use Whoa\Contracts\Authentication\AccountInterface;
use Whoa\Contracts\Authentication\AccountManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function assert;
use function is_array;
use function is_string;

/**
 * @package Whoa\Application
 */
trait AuthorizationRulesTrait
{
    /**
     * @param ContextInterface $context
     * @return bool
     */
    protected static function reqHasAction(ContextInterface $context): bool
    {
        return $context->getRequest()->has(RequestProperties::REQ_ACTION);
    }

    /**
     * @param ContextInterface $context
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected static function reqGetAction(ContextInterface $context): string
    {
        assert(static::reqHasAction($context));

        return $context->getRequest()->get(RequestProperties::REQ_ACTION);
    }

    /**
     * @param ContextInterface $context
     * @return bool
     */
    protected static function reqHasResourceType(ContextInterface $context): bool
    {
        return $context->getRequest()->has(RequestProperties::REQ_RESOURCE_TYPE);
    }

    /**
     * @param ContextInterface $context
     * @return string|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected static function reqGetResourceType(ContextInterface $context): ?string
    {
        assert(static::reqHasResourceType($context));

        $value = $context->getRequest()->get(RequestProperties::REQ_RESOURCE_TYPE);

        assert($value === null || is_string($value));

        return $value;
    }

    /**
     * @param ContextInterface $context
     * @return bool
     */
    protected static function reqHasResourceIdentity(ContextInterface $context): bool
    {
        return $context->getRequest()->has(RequestProperties::REQ_RESOURCE_IDENTITY);
    }

    /**
     * @param ContextInterface $context
     * @return string|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected static function reqGetResourceIdentity(ContextInterface $context): ?string
    {
        assert(static::reqHasResourceIdentity($context));

        $value = $context->getRequest()->get(RequestProperties::REQ_RESOURCE_IDENTITY);

        assert($value === null || is_string($value));

        return $value;
    }

    /**
     * @param ContextInterface $context
     * @return bool
     */
    protected static function reqHasResourceAttributes(ContextInterface $context): bool
    {
        return $context->getRequest()->has(RequestProperties::REQ_RESOURCE_ATTRIBUTES);
    }

    /**
     * @param ContextInterface $context
     * @return string|int|array|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected static function reqGetResourceAttributes(ContextInterface $context): array
    {
        assert(static::reqHasResourceAttributes($context));

        $value = $context->getRequest()->get(RequestProperties::REQ_RESOURCE_ATTRIBUTES);

        assert(is_array($value));

        return $value;
    }

    /**
     * @param ContextInterface $context
     * @return bool
     */
    protected static function reqHasResourceRelationships(ContextInterface $context): bool
    {
        return $context->getRequest()->has(RequestProperties::REQ_RESOURCE_RELATIONSHIPS);
    }

    /**
     * @param ContextInterface $context
     * @return string|int|array|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected static function reqGetResourceRelationships(ContextInterface $context): array
    {
        assert(static::reqHasResourceRelationships($context));

        $value = $context->getRequest()->get(RequestProperties::REQ_RESOURCE_RELATIONSHIPS);

        assert(is_array($value));

        return $value;
    }

    /**
     * @param ContextInterface $context
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected static function ctxHasCurrentAccount(ContextInterface $context): bool
    {
        /** @var AccountManagerInterface $manager */
        $container = static::ctxGetContainer($context);
        $manager = $container->get(AccountManagerInterface::class);
        $account = $manager->getAccount();

        return $account !== null;
    }

    /**
     * @param ContextInterface $context
     * @return AccountInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected static function ctxGetCurrentAccount(ContextInterface $context): AccountInterface
    {
        assert(static::ctxHasCurrentAccount($context));

        /** @var AccountManagerInterface $manager */
        $container = static::ctxGetContainer($context);
        $manager = $container->get(AccountManagerInterface::class);
        return $manager->getAccount();
    }

    /**
     * @param ContextInterface $context
     * @return bool
     */
    protected static function ctxHasContainer(ContextInterface $context): bool
    {
        return $context->has(ContextProperties::CTX_CONTAINER);
    }

    /**
     * @param ContextInterface $context
     * @return ContainerInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected static function ctxGetContainer(ContextInterface $context): ContainerInterface
    {
        assert(static::ctxHasContainer($context));

        return $context->get(ContextProperties::CTX_CONTAINER);
    }
}
