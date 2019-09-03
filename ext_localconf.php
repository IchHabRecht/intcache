<?php
defined('TYPO3_MODE') || die();

call_user_func(function () {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['tx_intcache_int'] = [
        'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
        'backend' => \TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend::class,
        'options' => [
            'compression' => true,
            'defaultLifetime' => 864000, // 10 days
        ],
        'groups' => ['system'],
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['createHashBase']['intcache'] =
        \IchHabRecht\Intcache\Hooks\CreateHashBaseHook::class . '->createHashBase';
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all']['intcache'] =
        \IchHabRecht\Intcache\Hooks\ContentPostProcessHook::class . '->replaceIntScripts';
});
