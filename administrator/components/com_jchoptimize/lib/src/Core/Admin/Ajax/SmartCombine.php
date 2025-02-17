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

namespace JchOptimize\Core\Admin\Ajax;

use JchOptimize\Core\Admin\AbstractHtml;
use JchOptimize\Core\Admin\Json;
use JchOptimize\Core\Admin\MultiSelectItems;

\defined('_JCH_EXEC') or exit('Restricted access');
class SmartCombine extends \JchOptimize\Core\Admin\Ajax\Ajax
{
    public function run(): Json
    {
        $container = $this->getContainer();

        /** @var MultiSelectItems $oAdmin */
        $oAdmin = $container->get(MultiSelectItems::class);

        /** @var AbstractHtml $oHtml */
        $oHtml = $container->get(AbstractHtml::class);

        try {
            $aHtml = $oHtml->getCrawledHtmls(['crawl_limit' => 3]);
            $aLinksArray = [];
            foreach ($aHtml[0] as $sHtml) {
                $aLinks = $oAdmin->generateAdminLinks($sHtml['html'], '', \true);
                if (isset($aLinks['css'][0], $aLinks['js'][0])) {
                    $aLinks['css'] = $this->setUpArray($aLinks['css'][0]);
                    $aLinks['js'] = $this->setUpArray($aLinks['js'][0]);
                    $aLinksArray[] = $aLinks;
                }
            }
            $aReturnArray = ['css' => $aLinksArray[0]['css'] ?: [], 'js' => $aLinksArray[0]['js'] ?: []];
            for ($i = 1; $i < \count($aLinksArray); ++$i) {
                $aReturnArray['css'] = \array_filter(\array_intersect($aReturnArray['css'], $aLinksArray[$i]['css']), function ($sUrl) {
                    return !\preg_match('#fonts\\.googleapis\\.com#i', $sUrl);
                });
                $aReturnArray['js'] = \array_intersect($aReturnArray['js'], $aLinksArray[$i]['js']);
            }
        } catch (\Exception $oException) {
            return new Json([]);
        }

        return new Json($aReturnArray);
    }

    protected function setUpArray($aLinks): array
    {
        return \array_map(function ($sValue) {
            return \preg_replace('#[?\\#].*+#i', '', $sValue);
        }, \array_column($aLinks, 'url'));
    }
}
