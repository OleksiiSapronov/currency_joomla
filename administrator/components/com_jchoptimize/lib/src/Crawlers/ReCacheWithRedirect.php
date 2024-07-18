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

namespace JchOptimize\Crawlers;

use _JchOptimizeVendor\GuzzleHttp\Exception\RequestException;
use _JchOptimizeVendor\Psr\Http\Message\ResponseInterface;
use _JchOptimizeVendor\Psr\Http\Message\UriInterface;
use _JchOptimizeVendor\Spatie\Crawler\CrawlObservers\CrawlObserver;
use JchOptimize\Core\SystemUri;
use JchOptimize\GetApplicationTrait;
use Joomla\Application\Web\WebClient;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

class ReCacheWithRedirect extends CrawlObserver
{
    use GetApplicationTrait;

    /**
     * @var string Url to redirect to
     */
    protected string $redirectUrl;

    public function __construct(string $redirectUrl = '')
    {
        $this->redirectUrl = $redirectUrl;
    }

    public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null)
    {
        if ((string) $url == SystemUri::currentBaseFull()) {
            $app = self::getApplication();
            $app->enqueueMessage(Text::_('COM_JCHOPTIMIZE_RECACHE_STARTED'), 'success');
            // Redirect without closing to allow recache to continue asynchronously.
            $this->redirect();
        }
    }

    public function crawlFailed(UriInterface $url, RequestException $requestException, ?UriInterface $foundOnUrl = null)
    {
        if ((string) $url == SystemUri::currentBaseFull()) {
            /** @var AdministratorApplication $app */
            $app = self::getApplication();
            $app->enqueueMessage(Text::_('COM_JCHOPTIMIZE_RECACHE_FAILED'), 'error');
            $app->redirect(Route::_('index.php?option=com_jchoptimize&view=PageCache', \false));
        }
    }

    private function redirect(): void
    {
        \ignore_user_abort(\true);
        $app = self::getApplication();
        // persist messages if they exist
        $messageQueue = $app->getMessageQueue();
        if (\count($messageQueue)) {
            $app->getSession()->set('application.queue', $messageQueue);
        }
        if (\headers_sent()) {
            echo '<script>document.location.href='.\json_encode($this->redirectUrl).";</script>\n";
        } elseif (WebClient::TRIDENT == $app->client->engine && !$app::isAscii($this->redirectUrl)) {
            $html = '<html><head>';
            $html .= '<meta http-equiv="content-type" content="text/html; charset='.$app->charSet.'" />';
            $html .= '<script>document.location.href='.\json_encode($this->redirectUrl).';</script>';
            $html .= '</head><body></body></html>';
            echo $html;
        } else {
            \ob_end_clean();
            $app->setBody('Redirecting...');
            $app->setHeader('Status', '303', \true);
            $app->setHeader('Location', $this->redirectUrl, \true);
            // Set no cache header
            $app->setHeader('Expires', 'Wed, 17 Aug 2005 00:00:00 GMT', \true);
            // Always modified.
            $app->setHeader('Last-Modified', \gmdate('D, d M Y H:i:s').' GMT', \true);
            $app->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0', \false);
            // HTTP 1.0
            $app->setHeader('Pragma', 'no-cache');
            $app->setHeader('Connection', 'close');
            $app->setHeader('Content-Length', (string) \strlen($app->getBody()));
            $app->sendHeaders();
            echo $app->getBody();
            $app->getSession()->close();
            Factory::getDbo()->disconnect();
            if (\function_exists('fastcgi_finish_request')) {
                \fastcgi_finish_request();
            } else {
                \ob_end_flush();
                \flush();
            }
        }
    }
}
