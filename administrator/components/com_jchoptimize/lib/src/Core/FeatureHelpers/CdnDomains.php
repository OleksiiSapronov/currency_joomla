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

namespace JchOptimize\Core\FeatureHelpers;

use _JchOptimizeVendor\Joomla\DI\Container;
use _JchOptimizeVendor\Laminas\EventManager\Event;
use _JchOptimizeVendor\Psr\Http\Message\UriInterface;
use JchOptimize\Core\Cdn;
use JchOptimize\Core\Html\LinkBuilder;
use Joomla\Registry\Registry;

\defined('_JCH_EXEC') or exit('Restricted access');
class CdnDomains extends \JchOptimize\Core\FeatureHelpers\AbstractFeatureHelper
{
    private Cdn $cdn;

    public function __construct(Container $container, Registry $params, Cdn $cdn, LinkBuilder $linkBuilder)
    {
        parent::__construct($container, $params);
        $this->cdn = $cdn;
        $linkBuilder->getEventManager()->attach('postProcessHtml', [$this, 'preconnect'], 300);
    }

    /**
     * @param array<string, array{domain:UriInterface, extensions:string}> $domains
     */
    public function addCdnDomains(array &$domains): void
    {
        /** @var string $domain2 */
        $domain2 = $this->params->get('pro_cookielessdomain_2', '');
        if ('' != \trim($domain2)) {
            /** @var string[] $staticFiles2Array */
            $staticFiles2Array = $this->params->get('pro_staticfiles_2', Cdn::getStaticFiles());
            $sStaticFiles2String = \implode('|', $staticFiles2Array);
            $domains['domain2']['domain'] = $this->cdn->prepareDomain($domain2);
            $domains['domain2']['extensions'] = $sStaticFiles2String;
        }

        /** @var string $domain3 */
        $domain3 = $this->params->get('pro_cookielessdomain_3', '');
        if ('' != \trim($domain3)) {
            /** @var string[] $staticFiles3Array */
            $staticFiles3Array = $this->params->get('pro_staticfiles_3', Cdn::getStaticFiles());
            $sStaticFiles3String = \implode('|', $staticFiles3Array);
            $domains['domain3']['domain'] = $this->cdn->prepareDomain($domain3);
            $domains['domain3']['extensions'] = $sStaticFiles3String;
        }
    }

    public function preconnect(Event $event): void
    {
        if ($this->params->get('cookielessdomain_enable', '0') && $this->params->get('pro_cdn_preconnect', '1')) {
            $domains = $this->cdn->getCdnDomains();

            /** @var LinkBuilder $linkBuilder */
            $linkBuilder = $event->getTarget();
            foreach ($domains as $domainStaticFilesPair) {
                $domainUri = $domainStaticFilesPair['domain'];
                $linkBuilder->prependChildToHead($linkBuilder->getPreconnectLink($domainUri));
            }
        }
    }
}
