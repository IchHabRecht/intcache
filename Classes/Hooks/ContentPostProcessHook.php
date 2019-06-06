<?php
namespace IchHabRecht\Intcache\Hooks;

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
use IchHabRecht\Intcache\Renderer\IntObjectRenderer;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class ContentPostProcessHook
{
    /**
     * @var ApplicationContext
     */
    protected $applicationContext;

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
        ApplicationContext $applicationContext = null,
        HashService $hashService = null,
        FrontendInterface $intCache = null,
        IntObjectRenderer $intObjectRenderer = null
    ) {
        $this->applicationContext = $applicationContext ?: GeneralUtility::getApplicationContext();
        $this->hashService = $hashService ?: GeneralUtility::makeInstance(HashService::class);
        $this->intCache = $intCache ?: GeneralUtility::makeInstance(CacheManager::class)->getCache('tx_intcache_int');
        $this->intObjectRenderer = $intObjectRenderer ?: GeneralUtility::makeInstance(IntObjectRenderer::class);
    }

    public function replaceIntScripts(array $parameter)
    {
        $this->typoScriptFrontendController = $parameter['pObj'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            || empty($this->typoScriptFrontendController->config['INTincScript'])
        ) {
            return;
        }

        if (empty($this->typoScriptFrontendController->tmpl->setup['lib.']['intcache.']['settings.']['typeNum'])) {
            throw new Exception('Page TypeNum for INT rendering must be set', 1559647300);
        }

        $cacheTag = 'newHash_' . $this->typoScriptFrontendController->newHash;
        $this->intCache->flushByTag($cacheTag);

        $content = $this->typoScriptFrontendController->content;
        foreach ($this->typoScriptFrontendController->config['INTincScript'] as $identifier => $configuration) {
            $matches = [];
            if (preg_match('/<!--\s*' . preg_quote($identifier) . '\s*-->/i', $content, $matches)) {
                $cacheIdentifier = md5(json_encode($configuration) . $cacheTag);
                $this->intCache->set($cacheIdentifier, $configuration, [$cacheTag]);

                $contentObjectRenderer = (!empty($configuration['cObj']))
                    ? unserialize($configuration['cObj'])
                    : $this->typoScriptFrontendController->cObj;
                if (!$contentObjectRenderer instanceof ContentObjectRenderer) {
                    $contentObjectRenderer = $this->typoScriptFrontendController->cObj;
                }

                $intcacheContent = '';
                if ($this->applicationContext->isDevelopment()) {
                    $intcacheContent = $this->intObjectRenderer->render($configuration);
                }

                $addQueryStringMethod = $this->typoScriptFrontendController->cHash ? 'GET' : '';
                $intcacheLink = $contentObjectRenderer->typoLink_URL(
                    [
                        'parameter' => implode(',', [
                            $this->typoScriptFrontendController->id,
                            $this->typoScriptFrontendController->tmpl->setup['lib.']['intcache.']['settings.']['typeNum'],
                        ]),
                        'forceAbsoluteUrl' => 1,
                        'addQueryString.' => [
                            'method' => $addQueryStringMethod,
                        ],
                        'additionalParams' => '&tx_intcache[identifier]=' . $this->hashService->appendHmac($cacheIdentifier),
                        'useCacheHash' => 1,
                    ]
                );

                $intcacheIdentifier = $this->typoScriptFrontendController->uniqueHash('intcache');

                $data = array_merge(
                    (array) $contentObjectRenderer->data,
                    [
                        'intcache_content' => $intcacheContent,
                        'intcache_identifier' => $intcacheIdentifier,
                        'intcache_link' => $intcacheLink,
                    ]
                );

                $contentObjectRenderer->start($data, $contentObjectRenderer->getCurrentTable());
                $intContent = $contentObjectRenderer->cObjGetSingle($this->typoScriptFrontendController->tmpl->setup['lib.']['intcache'], $this->typoScriptFrontendController->tmpl->setup['lib.']['intcache.']);
                $content = str_replace($matches[0], $intContent, $content);
            }
            unset($this->typoScriptFrontendController->config['INTincScript'][$identifier]);
        }

        $this->typoScriptFrontendController->content = $content;

        if (empty($this->typoScriptFrontendController->config['INTincScript'])) {
            // Add additional header parts
            $this->typoScriptFrontendController->INTincScript();
            unset($this->typoScriptFrontendController->config['INTincScript']);
        }
    }
}
