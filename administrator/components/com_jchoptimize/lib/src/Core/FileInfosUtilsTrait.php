<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads.
 *
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core;

\defined('_JCH_EXEC') or exit('Restricted access');
trait FileInfosUtilsTrait
{
    /**
     * @var FileUtils
     */
    private \JchOptimize\Core\FileUtils $fileUtils;

    /**
     * Truncate url at the '/' less than 40 characters prepending '...' to the string.
     */
    public function prepareFileUrl(array $fileInfos, string $type): string
    {
        return isset($fileInfos['url']) ? $this->fileUtils->prepareForDisplay($fileInfos['url'], '', \true, 40) : ('css' == $type ? 'Style' : 'Script').' Declaration';
    }
}
