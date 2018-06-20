<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Lock\Tests\Store;

use Symfony\Component\Lock\Store\SemaphoreStore;

/**
 * @author Jérémy Derussé <jeremy@derusse.com>
 *
 * @requires extension sysvsem
 */
class SemaphoreStoreTest extends AbstractStoreTest
{
    use BlockingStoreTestTrait;

    /**
     * {@inheritdoc}
     */
    protected function getStore()
    {
        return new SemaphoreStore();
    }
}
