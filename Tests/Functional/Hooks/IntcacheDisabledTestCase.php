<?php

namespace IchHabRecht\Intcache\Tests\Functional\Hooks;

/*
 * This file is part of the TYPO3 extension intcache.
 *
 * (c) Nicole Cordes <typo3@cordes.co>
 * It originated from the EXT:vcc package (https://packagist.org/packages/cpsit/vcc)
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use IchHabRecht\Intcache\Tests\Functional\AbstractFunctionalTestCase;

class IntcacheDisabledTestCase extends AbstractFunctionalTestCase
{
    /**
     * @test
     */
    public function intcacheIsDisabled()
    {
        $response = $this->getFrontendResponseWithQuery(1, 0, 0, 0, true, 0, '&type=2');

        $this->assertSame('success', $response->getStatus());
        $this->assertSame('Hello world!', $response->getContent());
    }
}
