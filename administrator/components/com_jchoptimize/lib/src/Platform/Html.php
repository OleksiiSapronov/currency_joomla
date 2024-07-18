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

namespace JchOptimize\Platform;

use _JchOptimizeVendor\GuzzleHttp\Exception\GuzzleException;
use _JchOptimizeVendor\GuzzleHttp\Psr7\Uri;
use JchOptimize\Core\Admin\AbstractHtml;
use JchOptimize\Core\Exception;
use JchOptimize\Core\Uri\Utils;
use JchOptimize\GetApplicationTrait;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\Menu\MenuItem;
use Joomla\CMS\Router\Route;

\defined('_JEXEC') or exit('Restricted access');
class Html extends AbstractHtml
{
    use GetApplicationTrait;

    /**
     * Returns HTML of the front page.
     *
     * @throws \Exception
     */
    public function getHomePageHtml(): string
    {
        try {
            JCH_DEBUG ? \JchOptimize\Platform\Profiler::mark('beforeGetHtml') : null;
            $response = $this->getHtml($this->getSiteUrl());
            JCH_DEBUG ? \JchOptimize\Platform\Profiler::mark('afterGetHtml') : null;

            return $response;
        } catch (Exception\ExceptionInterface $e) {
            $this->logger->error($this->getSiteUrl().': '.$e->getMessage());
            JCH_DEBUG ? \JchOptimize\Platform\Profiler::mark('afterGetHtml') : null;

            throw new Exception\RuntimeException('Try reloading the front page to populate the Exclude options');
        }
    }

    /**
     * @param mixed $iLimit
     * @param mixed $bIncludeUrls
     *
     * @throws \Exception
     */
    public function getMainMenuItemsHtmls($iLimit = 5, $bIncludeUrls = \false): array
    {
        $oSiteMenu = $this->getSiteMenu();
        $oDefaultMenu = $oSiteMenu->getDefault();
        $aAttributes = ['menutype', 'type', 'level', 'access', 'home'];
        $aValues = [$oDefaultMenu->menutype, 'component', '1', '1', '0'];
        // Only need 5 menu items including the home menu
        $aMenus = \array_slice(\array_merge([$oDefaultMenu], $oSiteMenu->getItems($aAttributes, $aValues)), 0, $iLimit);
        $aHtmls = [];
        // Gonna limit the time spent on this
        $iTimerStart = \microtime(\true);

        /** @var MenuItem $oMenuItem */
        foreach ($aMenus as $oMenuItem) {
            $oMenuItem->link = $this->getMenuUrl($oMenuItem);

            try {
                if ($bIncludeUrls) {
                    $aHtmls[] = ['url' => $oMenuItem->link, 'html' => $this->getHtml($oMenuItem->link)];
                } else {
                    $aHtmls[] = $this->getHtml($oMenuItem->link);
                }
            } catch (Exception\ExceptionInterface $e) {
                $this->logger->error($e->getMessage());
            }
            if (\microtime(\true) > $iTimerStart + 10.0) {
                break;
            }
        }

        return $aHtmls;
    }

    /**
     * @psalm-suppress UndefinedInterfaceMethod
     */
    protected function getHtml(string $sUrl): string
    {
        $uri = Utils::uriFor($sUrl);
        $unOptimizedUri = Uri::withQueryValues($uri, ['jchnooptimize' => '1']);

        try {
            $response = $this->http->get($unOptimizedUri);
        } catch (GuzzleException $e) {
            throw new Exception\RuntimeException('Exception fetching HTML: '.$sUrl.' - Message: '.$e->getMessage());
        }
        if (200 != $response->getStatusCode()) {
            throw new Exception\RuntimeException('Failed fetching HTML: '.$sUrl.' - Message: '.$response->getStatusCode().': '.$response->getReasonPhrase());
        }
        // Get body and set pointer to beginning of stream
        $body = $response->getBody();
        $body->rewind();

        return $body->getContents();
    }

    /**
     * @throws \Exception
     *
     * @psalm-suppress TooManyArguments
     */
    protected function getSiteUrl(): string
    {
        $oSiteMenu = $this->getSiteMenu();
        $oDefaultMenu = $oSiteMenu->getDefault();
        if (\is_null($oDefaultMenu)) {
            $oCompParams = ComponentHelper::getParams('com_languages');
            $sLanguage = $oCompParams->get('site', Factory::getApplication('site')->get('language', 'en-GB'));
            $oDefaultMenu = $oSiteMenu->getItems(['home', 'language'], ['1', $sLanguage], \true);
        }

        return $this->getMenuUrl($oDefaultMenu);
    }

    /**
     * @throws \Exception
     *
     * @psalm-suppress TooManyArguments
     */
    protected function getSiteMenu(): AbstractMenu
    {
        /** @var SiteApplication $app */
        $app = Factory::getApplication('site');

        return $app->getMenu('site');
    }

    /**
     * @psalm-suppress UndefinedMethod
     * @psalm-suppress UndefinedConstant
     */
    protected function getMenuUrl(MenuItem $oMenuItem): string
    {
        $oSiteRouter = SiteApplication::getRouter();
        $bSefModeTest = \version_compare(JVERSION, '4.0', '<') && JROUTER_MODE_SEF == $oSiteRouter->getMode();
        $sMenuUrl = $bSefModeTest ? 'index.php?Itemid='.$oMenuItem->id : $oMenuItem->link.'&Itemid='.$oMenuItem->id;

        return Route::link('site', $sMenuUrl, \true, 0, \true);
    }
}
