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

use JchOptimize\Core\Interfaces\Utility as UtilityInterface;
use JchOptimize\GetApplicationTrait;
use Joomla\Application\Web\WebClient;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session;
use Joomla\Registry\Registry;

\defined('_JEXEC') or exit('Restricted access');
class Utility implements UtilityInterface
{
    use GetApplicationTrait;

    public static function translate(string $text): string
    {
        if (\strlen($text) > 20) {
            $strpos = \strpos(\wordwrap($text, 20), "\n");
            if (\false !== $strpos) {
                $text = \substr($text, 0, $strpos);
            }
        }
        $text = 'COM_JCHOPTIMIZE_'.\strtoupper(\str_replace([' ', '\''], ['_', ''], $text));

        return Text::_($text);
    }

    /**
     * @throws \Exception
     */
    public static function isGuest(): bool
    {
        if (\version_compare(JVERSION, '4.0', 'gt')) {
            return (bool) self::getApplication()->getIdentity()->guest;
        }

        return (bool) Factory::getUser()->guest;
    }

    public static function sendHeaders(array $headers): void
    {
        /** @psalm-var array{name:string} $headers */
        $app = self::getApplication();
        foreach ($headers as $header => $value) {
            $app->setHeader($header, $value, \true);
        }
    }

    public static function userAgent(string $userAgent): \stdClass
    {
        $oWebClient = new WebClient($userAgent);
        $oUA = new \stdClass();

        switch ($oWebClient->browser) {
            case $oWebClient::CHROME:
                $oUA->browser = 'Chrome';

                break;

            case $oWebClient::FIREFOX:
                $oUA->browser = 'Firefox';

                break;

            case $oWebClient::SAFARI:
                $oUA->browser = 'Safari';

                break;

            case $oWebClient::EDGE:
                $oUA->browser = 'Edge';

                break;

            case $oWebClient::IE:
                $oUA->browser = 'Internet Explorer';

                break;

            case $oWebClient::OPERA:
                $oUA->browser = 'Opera';

                break;

            default:
                $oUA->browser = 'Unknown';

                break;
        }
        $oUA->browserVersion = $oWebClient->browserVersion;

        switch ($oWebClient->platform) {
            case $oWebClient::ANDROID:
            case $oWebClient::ANDROIDTABLET:
                $oUA->os = 'Android';

                break;

            case $oWebClient::IPAD:
            case $oWebClient::IPHONE:
            case $oWebClient::IPOD:
                $oUA->os = 'iOS';

                break;

            case $oWebClient::MAC:
                $oUA->os = 'Mac';

                break;

            case $oWebClient::WINDOWS:
            case $oWebClient::WINDOWS_CE:
            case $oWebClient::WINDOWS_PHONE:
                $oUA->os = 'Windows';

                break;

            case $oWebClient::LINUX:
                $oUA->os = 'Linux';

                break;

            default:
                $oUA->os = 'Unknown';

                break;
        }

        return $oUA;
    }

    /**
     * Should return the attribute used to store content values for popover that the version of Bootstrap
     * is using.
     */
    public static function bsTooltipContentAttribute(): string
    {
        return \version_compare(JVERSION, '3.99.99', '<') ? 'data-content' : 'data-bs-content';
    }

    /**
     * @deprecated Use Cache::isPageCacheEnabled()
     */
    public static function isPageCacheEnabled(Registry $params, bool $nativeCache = \false): bool
    {
        return PluginHelper::isEnabled('system', 'jchoptimizepagecache');
    }

    public static function isMobile(): bool
    {
        $webClient = new WebClient();

        return $webClient->mobile;
    }

    /**
     * @deprecated Use Cache::getCacheStorage()
     */
    public static function getCacheStorage(Registry $params): string
    {
        switch ($params->get('pro_cache_storage_adapter', 'filesystem')) {
            // Used in Unit testing.
            case 'blackhole':
                return 'blackhole';

            case 'global':
                $storageMap = ['file' => 'filesystem', 'redis' => 'redis', 'apcu' => 'apcu', 'memcached' => 'memcached', 'wincache' => 'wincache'];
                $app = self::getApplication();

                /** @var string $handler */
                $handler = $app->get('cache_handler', 'file');
                if (\in_array($handler, \array_keys($storageMap))) {
                    return $storageMap[$handler];
                }
                // no break
            case 'filesystem':
            default:
                return 'filesystem';
        }
    }

    /**
     * @return array<array{header:string, value:string}>
     */
    public static function getHeaders(): array
    {
        return self::getApplication()->getHeaders();
    }

    public static function publishAdminMessages(string $message, string $messageType)
    {
        self::getApplication()->enqueueMessage($message, $messageType);
    }

    public static function getLogsPath(): string
    {
        /** @var string $logPath */
        return self::getApplication()->get('log_path');
    }

    public static function isSiteGzipEnabled(): bool
    {
        return self::getApplication()->get('gzip') && !\ini_get('zlib.output_compression') && 'ob_gzhandler' !== \ini_get('output_handler');
    }

    /**
     * @psalm-suppress all
     *
     * @depecated Use Cache::prepareDataFromCache()
     */
    public static function prepareDataFromCache(?array $data): ?array
    {
        // The following code searches for a token in the cached page and replaces it with the proper token.
        if (isset($data['body'])) {
            $token = Session::getFormToken();
            $search = '#<input type="?hidden"? name="?[\\da-f]{32}"? value="?1"?\\s*/?>#';
            $replacement = '<input type="hidden" name="'.$token.'" value="1">';
            $data['body'] = \preg_replace($search, $replacement, $data['body']);
        }

        return $data;
    }

    /**
     * @throws \Exception
     *
     * @psalm-suppress all
     *
     * @deprecated Use Cache::outputData()
     */
    public static function outputData(array $data): void
    {
        $app = self::getApplication();
        if (!empty($data['headers'])) {
            foreach ($data['headers'] as $header) {
                $app->setHeader($header['name'], $header['value']);
            }
        }
        $app->setBody($data['body']);
        echo $app->toString((bool) $app->get('gzip'));
        $app->close();
    }

    public static function isAdmin(): bool
    {
        return self::getApplication()->isClient('administrator');
    }

    public static function getNonce(string $id): string
    {
        return '';
    }
}
