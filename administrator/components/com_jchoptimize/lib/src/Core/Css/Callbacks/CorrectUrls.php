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

namespace JchOptimize\Core\Css\Callbacks;

use _JchOptimizeVendor\GuzzleHttp\Psr7\Uri;
use _JchOptimizeVendor\GuzzleHttp\Psr7\UriResolver;
use _JchOptimizeVendor\Joomla\DI\Container;
use JchOptimize\Core\Cdn;
use JchOptimize\Core\Css\Parser;
use JchOptimize\Core\FeatureHelpers\LazyLoadExtended;
use JchOptimize\Core\FeatureHelpers\Webp;
use JchOptimize\Core\Http2Preload;
use JchOptimize\Core\SystemUri;
use JchOptimize\Core\Uri\UriComparator;
use JchOptimize\Core\Uri\Utils;
use Joomla\Registry\Registry;

\defined('_JCH_EXEC') or exit('Restricted access');
class CorrectUrls extends \JchOptimize\Core\Css\Callbacks\AbstractCallback
{
    /** @var bool True if this callback is called when preloading assets for HTTP/2 */
    public bool $isHttp2 = \false;

    /** @var bool If Optimize CSS Delivery is disabled, only fonts are preloaded */
    public bool $isFontsOnly = \false;

    /** @var bool If run from admin we populate the array */
    public bool $isBackend = \false;

    public Cdn $cdn;

    public Http2Preload $http2Preload;
    public array $cssBgImagesSelectors = [];

    private array $images = [];

    /** @var array An array of external domains that we'll add preconnects for */
    private array $preconnects = [];

    private array $cssInfos;

    public function __construct(Container $container, Registry $params, Cdn $cdn, Http2Preload $http2Preload)
    {
        parent::__construct($container, $params);
        $this->cdn = $cdn;
        $this->http2Preload = $http2Preload;
    }

    public function processMatches(array $matches, string $context): string
    {
        $sRegex = '(?>u?[^u]*+)*?\\K(?:'.Parser::cssUrlWithCaptureValueToken(\true).'|$)';
        if ('import' == $context) {
            $sRegex = Parser::cssAtImportWithCaptureValueToken(\true);
        }
        $css = \preg_replace_callback('#'.$sRegex.'#i', function ($aInnerMatches) use ($context) {
            return $this->processInnerMatches($aInnerMatches, $context);
        }, $matches[0]);
        // Lazy-load background images
        if (JCH_PRO && $this->params->get('lazyload_enable', '0') && $this->params->get('pro_lazyload_bgimages', '0') && !\in_array($context, ['font-face', 'import'])) {
            // @see LazyLoadExtended::handleCssBgImages()
            return $this->getContainer()->get(LazyLoadExtended::class)->handleCssBgImages($this, $css);
        }

        return $css;
    }

    public function setCssInfos($cssInfos): void
    {
        $this->cssInfos = $cssInfos;
    }

    public function getImages(): array
    {
        return $this->images;
    }

    public function getPreconnects(): array
    {
        return $this->preconnects;
    }

    public function getCssBgImagesSelectors(): array
    {
        return $this->cssBgImagesSelectors;
    }

    /**
     * @param string[] $matches
     * @param mixed    $context
     *
     * @psalm-param array<string> $matches
     */
    protected function processInnerMatches(array $matches, $context)
    {
        if (empty($matches[0])) {
            return $matches[0];
        }
        $originalUri = Utils::uriFor($matches[1]);
        if ('data' !== $originalUri->getScheme() && '' != $originalUri->getPath() && '/' != $originalUri->getPath()) {
            // The urls were already corrected on a previous run, we're only preloading assets in critical CSS and return
            if ($this->isHttp2) {
                $sFileType = 'font-face' == $context ? 'font' : 'image';
                // If Optimize CSS Delivery not enabled, we'll only preload fonts.
                if ($this->isFontsOnly && 'font' != $sFileType) {
                    return \false;
                }
                $this->http2Preload->add($originalUri, $sFileType);

                return \true;
            }
            // Get the url of the file that contained the CSS
            $cssFileUri = empty($this->cssInfos['url']) ? new Uri() : $this->cssInfos['url'];
            $cssFileUri = UriResolver::resolve(SystemUri::currentUri(), $cssFileUri);
            $imageUri = UriResolver::resolve($cssFileUri, $originalUri);
            if (!UriComparator::isCrossOrigin($imageUri)) {
                $imageUri = $this->cdn->loadCdnResource($imageUri);
            } elseif ($this->params->get('pro_optimizeFonts_enable', '0')) {
                // Cache external domains to add preconnects for them
                $domain = Uri::composeComponents($imageUri->getScheme(), $imageUri->getAuthority(), '', '', '');
                if (!\in_array($domain, $this->preconnects)) {
                    $this->preconnects[] = $domain;
                }
            }
            if ($this->isBackend && 'font-face' != $context) {
                $this->images[] = $imageUri;
            }
            if (JCH_PRO && $this->params->get('pro_load_webp_images', '0')) {
                /** @see Webp::getWebpImages() */
                $imageUri = $this->getContainer()->get(Webp::class)->getWebpImages($imageUri) ?? $imageUri;
            }
            // If URL without quotes and contains any parentheses, whitespace characters,
            // single quotes (') and double quotes (") that are part of the URL, quote URL
            if (\false !== \strpos($matches[0], 'url('.$originalUri.')') && \preg_match('#[()\\s\'"]#', $imageUri)) {
                $imageUri = '"'.$imageUri.'"';
            }

            return \str_replace($matches[1], $imageUri, $matches[0]);
        }

        return $matches[0];
    }
}
