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

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class IntObjectRenderer
{
    /**
     * @param array $configuration
     * @return string
     */
    public function render(array $configuration)
    {
        if (empty($configuration['cObj']) || empty($configuration['type'])) {
            return '';
        }

        $contentObjectRenderer = unserialize($configuration['cObj']);
        if (!$contentObjectRenderer instanceof ContentObjectRenderer) {
            return '';
        }

        $content = '';
        switch ($configuration['type']) {
            case 'COA':
                $content = $contentObjectRenderer->cObjGetSingle('COA', $configuration['conf']);
                break;
            case 'FUNC':
                $content = $contentObjectRenderer->cObjGetSingle('USER', $configuration['conf']);
                break;
            case 'POSTUSERFUNC':
                $content = $contentObjectRenderer->callUserFunction($configuration['postUserFunc'], $configuration['conf'], $configuration['content']);
                break;
        }

        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->setTemplateFile('EXT:intcache/Resources/Private/Templates/PageRenderer.html');
        $page = $pageRenderer->render(PageRenderer::PART_HEADER);
        $page .= $content;
        // The PageRenderer gets reset after render, so the template must be reassigned
        $pageRenderer->setTemplateFile('EXT:intcache/Resources/Private/Templates/PageRenderer.html');
        $page .= $pageRenderer->render(PageRenderer::PART_FOOTER);

        return $page;
    }
}
