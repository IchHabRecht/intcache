<?php

namespace IchHabRecht\Intcache\Tests\Functional;

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

use Nimut\TestingFramework\Http\Response;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use PHPUnit\Util\PHP\DefaultPhpProcess;

abstract class AbstractFunctionalTestCase extends FunctionalTestCase
{
    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/intcache',
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->importDataSet('ntf://Database/pages.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:intcache/Configuration/TypoScript/setup.txt',
                'EXT:intcache/Tests/Functional/Fixtures/TypoScript/intcache.ts',
            ]
        );
    }

    protected function getFrontendResponseWithQuery($pageId, $languageId = 0, $backendUserId = 0, $workspaceId = 0, $failOnFailure = true, $frontendUserId = 0, $additionalParameter = '')
    {
        $pageId = (int)$pageId;
        $languageId = (int)$languageId;

        if (!empty($frontendUserId)) {
            $additionalParameter .= '&frontendUserId=' . (int)$frontendUserId;
        }
        if (!empty($backendUserId)) {
            $additionalParameter .= '&backendUserId=' . (int)$backendUserId;
        }
        if (!empty($workspaceId)) {
            $additionalParameter .= '&workspaceId=' . (int)$workspaceId;
        }

        $arguments = [
            'documentRoot' => $this->getInstancePath(),
            'requestUrl' => 'http://localhost/?id=' . $pageId . '&L=' . $languageId . $additionalParameter,
        ];

        $template = new \Text_Template('ntf://Frontend/Request.tpl');
        $template->setVar(
            [
                'arguments' => var_export($arguments, true),
                'originalRoot' => ORIGINAL_ROOT,
                'ntfRoot' => __DIR__ . '/../../',
            ]
        );

        $php = DefaultPhpProcess::factory();
        $response = $php->runJob($template->render());
        $result = json_decode($response['stdout'], true);

        if ($result === null) {
            $this->fail('Frontend Response is empty.' . LF . 'Error: ' . LF . $response['stderr']);
        }

        if ($failOnFailure && $result['status'] === Response::STATUS_Failure) {
            $this->fail('Frontend Response has failure:' . LF . $result['error']);
        }

        $response = new Response($result['status'], $result['content'], $result['error']);

        return $response;
    }
}
