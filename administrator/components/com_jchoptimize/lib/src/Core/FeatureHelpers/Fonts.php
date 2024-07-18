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
use JchOptimize\Core\Exception;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Html\CacheManager;
use JchOptimize\Core\Html\ElementObject;
use JchOptimize\Core\Html\LinkBuilder;
use JchOptimize\Core\Html\Parser;
use JchOptimize\Core\Html\Processor as HtmlProcessor;
use JchOptimize\Core\Uri\Utils;
use Joomla\Registry\Registry;
use Psr\Log\LoggerInterface;

\defined('_JCH_EXEC') or exit('Restricted access');
class Fonts extends \JchOptimize\Core\FeatureHelpers\AbstractFeatureHelper
{
    /**
     * @var bool Indicates that a Google Font file was captured to be optimized
     */
    public bool $isGoogleFontsOptimized = \false;

    /**
     * @var array Array of files containing @font-face content to be preloaded
     */
    public array $fonts = [];

    /**
     * @var array Array of external domains that we'll add a preconnect for
     */
    public array $preconnects = [];

    private LinkBuilder $linkBuilder;

    /**
     * @var bool If the Optimize Fonts feature is enabled
     */
    private bool $enable;

    public function __construct(Container $container, Registry $params, LinkBuilder $linkBuilder)
    {
        parent::__construct($container, $params);
        $this->linkBuilder = $linkBuilder;
        $this->enable = (bool) $params->get('pro_optimizeFonts_enable', '0');
    }

    public function generateCombinedFilesForFonts($cssCache): void
    {
        // If google font files were collected we can just add them straight to the fonts array property
        if (!empty($cssCache['gfonts'])) {
            $this->pushFilesToFontsArray($cssCache['gfonts']);
        }
        // If any @font-face content was captured then we combine them into a file and add the file to the fonts array property
        if (!empty($cssCache['font-face'])) {
            // Prepare info in a format the combiner expects
            $fontInfo = self::prepareFontsInfo($cssCache['font-face']);

            /** @var CacheManager $oCacheManager */
            $oCacheManager = $this->getContainer()->get(CacheManager::class);
            $oCacheManager->getCombinedFiles($fontInfo, $fontsId, 'css');
            $this->pushFileToFontsArray((string) $this->linkBuilder->buildUrl($fontsId, 'css'), '');
        }
        // Any external domains to preconnect is added to the preconnect array property
        if (!empty($cssCache['preconnects'])) {
            $this->pushDomainsToPreconnectArray($cssCache['preconnects']);
        }
    }

    /**
     * Iterates over an associated array of font file information and adds then to the font array property.
     */
    public function pushFilesToFontsArray(array $fontsInfo): void
    {
        if ($this->enable) {
            foreach ($fontsInfo as $fonts) {
                $url = $fonts['url'];
                $media = $fonts['media'];
                $this->pushFileToFontsArray($url, $media);
            }
        }
    }

    /**
     * Pushes a single file information to the associated font array property. If it's a Google Font file then the 'display=swap' will be added.
     */
    public function pushFileToFontsArray(string $url, string $media): void
    {
        if ($this->enable) {
            if (\false !== \strpos($url, 'fonts.googleapis.com')) {
                if (!\preg_match('#[?&;]display=#', $url)) {
                    $url .= '&display=swap';
                } else {
                    // Let's just go ahead and replace the display policy with swap
                    $url = \preg_replace('#(?<=[?&;])display=[^&]*+#', 'display=swap', $url);
                }
                $this->isGoogleFontsOptimized = \true;
            }
            if ('none' == $media) {
                $media = 'all';
            }
            $this->fonts[] = ['url' => $url, 'media' => $media];
        }
    }

    public function pushDomainsToPreconnectArray(array $domains): void
    {
        $this->preconnects = \array_unique(\array_merge($this->preconnects, \array_map(function ($domain) {
            return Utils::uriFor($domain);
        }, $domains)));
    }

    /**
     * Listener to prepend all font files to the HEAD section of the document on the postProcessHtml event.
     */
    public function appendOptimizedFontsToHead(Event $event)
    {
        foreach ($this->fonts as $font) {
            $this->linkBuilder->prependChildToHead($this->linkBuilder->getPreloadStyleSheet($font['url'], $font['media']));
        }
    }

    /**
     * Listener to prepend all external domains preconnect to the HEAD section of the document on the postProcessHtml event.
     */
    public function addPreConnectsFontsToHead(Event $event)
    {
        // If google fonts were optimized then add the fonts domain to preconnects if necessary
        if ($this->isGoogleFontsOptimized) {
            $this->pushDomainsToPreconnectArray(['https://fonts.gstatic.com']);
        }
        if ($this->enable) {
            $domains = Helper::getArray($this->params->get('pro_preconnect_domains', []));
            $this->pushDomainsToPreconnectArray($domains);
        }
        if (!empty($this->preconnects)) {
            foreach ($this->preconnects as $preconnect) {
                $this->linkBuilder->prependChildToHead($this->linkBuilder->getPreconnectLink($preconnect));
            }
        }
    }

    /**
     * This removes all current preconnects, saving the domains and adding them to the preconnects array to ensure
     * they are loaded properly, and there are no duplication.
     */
    public function checkPreconnects(): void
    {
        if ($this->enable) {
            try {
                $oGFParser = new Parser();
                $oGFParser->addExclude(Parser::htmlCommentToken());
                $oGFElement = new ElementObject();
                $oGFElement->setNamesArray(['link']);
                $oGFElement->addPosAttrCriteriaRegex('rel==[\'"]?preconnect[\'"> ]');
                $oGFElement->setCaptureAttributesArray(['href']);
                $oGFElement->bSelfClosing = \true;
                $oGFParser->addElementObject($oGFElement);

                /** @var HtmlProcessor $oProcessor */
                $oProcessor = $this->getContainer()->get(HtmlProcessor::class);
                $headHtml = $oProcessor->getHeadHtml();
                $aMatches = $oGFParser->findMatches($headHtml, \PREG_SET_ORDER);
                if (!empty($aMatches[0])) {
                    $existingPreconnects = \array_column($aMatches, 0);
                    $cleanedHeadHtml = \str_replace($existingPreconnects, '', $headHtml);
                    $oProcessor->setHeadHtml($cleanedHeadHtml);
                    $this->pushDomainsToPreconnectArray(\array_column($aMatches, 4));
                }
            } catch (Exception\ExceptionInterface $oException) {
                $logger = $this->getContainer()->get(LoggerInterface::class);
                $logger->error('Failed searching for Gfont preconnect: '.$oException->getMessage());
            }
        }
    }

    private function prepareFontsInfo($aFontFaceArray): array
    {
        $aFonts = [];
        foreach ($aFontFaceArray as $aFontFace) {
            $fontFaceCss = $aFontFace['content'];
            $aFonts[] = ['content' => $fontFaceCss, 'media' => $aFontFace['media'], 'combining-fontface' => \true];
        }

        return $aFonts;
    }
}
