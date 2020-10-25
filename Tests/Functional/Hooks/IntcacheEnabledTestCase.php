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

class IntcacheEnabledTestCase extends AbstractFunctionalTestCase
{
    /**
     * @test
     */
    public function intcacheIsEnabled()
    {
        $response = $this->getFrontendResponse(1);

        $this->assertSame('success', $response->getStatus());

        $content = $response->getContent();

        $this->assertContains('class="intcache intcache-item intcache-link"', $content);

        $matches = null;
        preg_match('/data-src="([^"]+)"/', $content, $matches);

        $this->assertArrayHasKey(1, $matches);

        $urlParams = parse_url($matches[1]);
        $this->assertArrayHasKey('query', $urlParams);

        $response = $this->getFrontendResponseWithQuery(1, 0, 0, 0, true, 0, '&' . $urlParams['query']);

        $this->assertSame('success', $response->getStatus());
        $this->assertSame('Hello world!', $response->getContent());
    }
}
