<?php
/**
 * @package         Cache Cleaner
 * @version         8.5.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

namespace RegularLabs\Plugin\System\CacheCleaner\Cache;

defined('_JEXEC') or die;

use JotCacheMainModelMain;

class JotCache extends Cache
{
    public static function purge()
    {
        $file = JPATH_ADMINISTRATOR . '/components/com_jotcache/models/main.php';

        if ( ! file_exists($file))
        {
            return;
        }

        require_once __DIR__ . '/JotCacheMainModelMain.php';

        $model = new JotCacheMainModelMain;
        $model->deleteall();
    }
}
