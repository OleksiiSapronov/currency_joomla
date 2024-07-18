<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads.
 *
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Model;

use _JchOptimizeVendor\Joomla\Model\StatefulModelInterface;
use _JchOptimizeVendor\Joomla\Model\StatefulModelTrait;

\defined('_JEXEC') or exit('Restricted Access');
class ApiParams implements StatefulModelInterface
{
    use StatefulModelTrait;
    private \JchOptimize\Model\Updates $updates;

    public function __construct(Updates $updates)
    {
        $this->updates = $updates;
    }

    public function getCompParams(): array
    {
        $apiParams = ['pro_downloadid' => $this->updates->getLicenseKey(), 'hidden_api_secret' => '0aad0284', 'ignore_optimized' => '1', 'recursive' => '1', 'pro_api_resize_mode' => '1', 'pro_next_gen_images' => '1', 'lossy' => '1', 'save_metadata' => '0'];
        $aSetParams = \array_intersect_key($this->state->toArray(), $apiParams);

        return \array_replace($apiParams, $aSetParams);
    }
}
