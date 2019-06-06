<?php
namespace IchHabRecht\Intcache\Renderer;

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

use IchHabRecht\Intcache\Exception\Exception;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class IntcacheRenderer
{
    /**
     * @var HashService
     */
    protected $hashService;

    /**
     * @var FrontendInterface
     */
    protected $intCache;

    /**
     * @var IntObjectRenderer
     */
    protected $intObjectRenderer;

    /**
     * @var TypoScriptFrontendController
     */
    protected $typoScriptFrontendController;

    public function __construct(
        HashService $hashService = null,
        FrontendInterface $intCache = null,
        IntObjectRenderer $intObjectRenderer = null,
        TypoScriptFrontendController $typoScriptFrontendController = null
    ) {
        $this->hashService = $hashService ?: GeneralUtility::makeInstance(HashService::class);
        $this->intCache = $intCache ?: GeneralUtility::makeInstance(CacheManager::class)->getCache('tx_intcache_int');
        $this->intObjectRenderer = $intObjectRenderer ?: GeneralUtility::makeInstance(IntObjectRenderer::class);
        $this->typoScriptFrontendController = $typoScriptFrontendController ?: $GLOBALS['TSFE'];
    }

    public function render()
    {
        $arguments = GeneralUtility::_GET('tx_intcache');
        if (empty($arguments['identifier']) || !is_string($arguments['identifier'])) {
            throw new  Exception('Missing identifier', 1559825922);
        }

        $cacheIdentifier = $this->hashService->validateAndStripHmac($arguments['identifier']);

        $configuration = $this->intCache->get($cacheIdentifier);

        if (empty($configuration)) {
            $this->setPageNotFound();

            return '';
        }

        if (!empty($configuration['conf']['cache_timeout'])) {
            $this->typoScriptFrontendController->page['cache_timeout'] = $configuration['conf']['cache_timeout'];
        } else {
            $this->typoScriptFrontendController->set_no_cache('Intcache response is non-cacheable by default');
        }

        return $this->intObjectRenderer->render($configuration);
    }

    protected function setPageNotFound()
    {
        $this->typoScriptFrontendController->set_no_cache('No intcache configuration found');
        if (empty($this->typoScriptFrontendController->config['config']['additionalHeaders.'])) {
            $this->typoScriptFrontendController->config['config']['additionalHeaders.'] = [];
        }
        $matches = [];
        preg_match('/\s+(\d+)\s+(.*)/', $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling_statheader'], $matches);
        $this->typoScriptFrontendController->config['config']['additionalHeaders.'] += [
            [
                'header' => !empty($matches[2]) ? $matches[2] : 'Not Found',
                'httpResponseCode' => !empty($matches[1]) ? (int)$matches[1] : 404,
                'replace' => '1',
            ],
        ];
    }
}
