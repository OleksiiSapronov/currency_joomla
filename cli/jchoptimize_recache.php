<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

use JchOptimize\ContainerFactory;
use JchOptimize\Model\Cache;
use JchOptimize\Model\ReCacheCliJ3;
use Joomla\CMS\Application\CliApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

const _JEXEC = 1;

error_reporting(E_ALL | E_NOTICE);
ini_set('display_errors', 1);

//load defines
if (file_exists(dirname(__DIR__) . '/defines.php')) {
    require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES')) {
    define('JPATH_BASE', dirname(__DIR__));
    require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_BASE . '/includes/framework.php';
require_once JPATH_ADMINISTRATOR . '/components/com_jchoptimize/autoload.php';

class JchOptimizeReCache extends CliApplication
{

    protected function doExecute()
    {
        $lang = Factory::getLanguage();
        $lang->load('com_jchoptimize', JPATH_ADMINISTRATOR);

        $liveSite = (string)($this->input->get('live-site', null, 'string') ?? $this->get('live_site', ''));

        $this->populateHttpHost($liveSite);

        //Gonna need to instantiate the site application
        Factory::getApplication('site');
        $container = ContainerFactory::getContainer();

        if (!$this->input->get('no-delete-cache')) {
            //First flush the cache
            /** @var Cache $cache */
            $cache = $container->get(Cache::class);
            $cache->cleanCache();
            $this->out(Text::_('COM_JCHOPTIMIZE_CLI_CACHE_CLEANED'));
        }

        $this->out(Text::_('COM_JCHOPTIMIZE_CLI_RECACHE_START'));

        $reCacheCliJ3 = $container->get(ReCacheCliJ3::class);
        $reCacheCliJ3->reCache($this, $liveSite);

        $this->out(Text::sprintf('COM_JCHOPTIMIZE_CLI_RECACHE_NUM_URLS_CRAWLED', $reCacheCliJ3->getObserver()->getNumCrawled()));
        $this->out(Text::_('COM_JCHOPTIMIZE_CLI_RECACHE_SUCCESS'));
    }

    protected function populateHttpHost($liveSite)
    {
        if ($liveSite == '') {
            $this->out(Text::_('COM_JCHOPTIMIZE_CLI_BASE_URL_NOT_SET'));
            $this->close();
        }
        /**
         * Try to use the live site URL we were given.
         */
        try {
            $uri = Uri::getInstance($liveSite);
        } catch (\RuntimeException $e) {
            $this->out('The \'live_site\' configuration value seems invalid.');
            $this->close();
        }

        /**
         * Yes, this is icky but it is the only way to trick WebApplication into compliance.
         *
         * @see \Joomla\Application\AbstractWebApplication::detectRequestUri
         */
        $_SERVER['HTTP_HOST'] = $uri->toString(['host', 'port']);
        $_SERVER['REQUEST_URI'] = $uri->getPath();
        $_SERVER['HTTPS'] = $uri->getScheme() === 'https' ? 'on' : 'off';
    }
}

CliApplication::getInstance('JchOptimizeReCache')->execute();
