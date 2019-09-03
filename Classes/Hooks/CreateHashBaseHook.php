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

class CreateHashBaseHook
{
    public function createHashBase(array $params)
    {
        $params['hashParameters']['requestMethod'] = $_SERVER['REQUEST_METHOD'] === 'POST' ? 'POST' : 'GET';
    }
}
