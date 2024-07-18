<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

use CodeAlfa\Minify\Js;
use JchOptimize\ContainerFactory;
use JchOptimize\Core\Container\Container;
use JchOptimize\Core\Helper;
use JchOptimize\Core\PageCache\PageCache;
use JchOptimize\Core\SystemUri;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\Event\DispatcherInterface;

defined('_JEXEC') or die ('Restricted access');

include_once JPATH_ADMINISTRATOR . '/components/com_jchoptimize/autoload.php';

class plgSystemJchoptimizepagecache extends CMSPlugin
{
    /**
     * If plugin is enabled
     *
     * @var bool
     */
    public bool $enabled = true;

    /**
     * Application object
     *
     * @var ?CMSApplicationInterface
     */
    protected $app = null;

    /**
     * Container object
     *
     * @var Container
     */
    private Container $container;

    /**
     * Page Cache object
     *
     * @var PageCache
     */
    private PageCache $pageCache;

    /**
     * Constructor
     *
     * @param DispatcherInterface $subject The object to observe
     * @param array $config Optional associative array of configurations
     */
    public function __construct(&$subject, $config = [])
    {
        parent::__construct($subject, $config);

        //Disable if the component is not installed or disabled
        if (!ComponentHelper::isEnabled('com_jchoptimize')) {
            $this->enabled = false;

            return;
        }

        //Disable if we can't get component's container
        try {
            $this->container = ContainerFactory::getContainer();
        } catch (Exception $e) {
            $this->enabled = false;

            return;
        }

        //Disable if client is not Site
        if (!$this->app->isClient('site')) {
            $this->enabled = false;

            return;
        }

        //Disable if site offline
        if ($this->app->get('offline', '0')) {
            $this->enabled = false;

            return;
        }

        //Disable if there are messages enqueued
        if ($this->app->getMessageQueue()) {
            $this->enabled = false;

            return;
        }

        //Disable if jchnooptimize set
        if ($this->app->input->get('jchnooptimize') == '1'
            || $this->app->input->get('jchbackend') == '1') {
            $this->enabled = false;

            return;
        }

        //Disable if we couldn't get cache object
        try {
            $this->pageCache = $this->container->get(PageCache::class);
        } catch (Exception $e) {
            //didn't work, disable
            $this->enabled = false;

            return;
        }
    }

    public function onAfterInitialise()
    {
        //If already disabled return
        if (!$this->enabled) {
            return;
        }

        if (JDEBUG) {
            $this->pageCache->disableCaptureCache();
        }

        $this->pageCache->initialize();
    }

    /**
     * After route event, have to check for excluded menu items here
     */
    public function onAfterRoute()
    {
        //If already disabled return
        if (!$this->enabled) {
            return;
        }

        //If we're forcing ssl on the front end but not serving https, disable caching
        if ($this->app->get('force_ssl') === 2 && SystemUri::currentUri()->getScheme() !== 'https') {
            $this->enabled = false;
            return;
        }

        try {
            $excludedMenus = $this->container->get('params')->get('cache_exclude_menu', []);
            $excludedComponents = $this->container->get('params')->get('cache_exclude_component', []);

            if (in_array($this->app->input->get('Itemid', '', 'int'), $excludedMenus)
                || in_array($this->app->input->get('option', ''), $excludedComponents)
            ) {
                $this->enabled = false;
                $this->pageCache->disableCaching();

                return;
            }
            //Now may be a good time to set Caching
            $this->pageCache->setCaching();
        } catch (Exception $e) {
        }
    }

    public function onAfterRender()
    {
        if (!$this->enabled) {
            return;
        }

        $html = $this->app->getBody();

        if (!Helper::validateHtml($html)) {
            $this->pageCache->disableCaching();

            return;
        }

        if ($this->pageCache->isCaptureCacheEnabled()) {
            $html = $this->addUpdateFormTokenAjax($html);
        }

        $this->app->setBody($html);

        //Disable gzip so the HTML can be cached later
        $this->app->set('gzip', false);
    }

    public function onAfterRespond()
    {
        if ($this->enabled) {
            //Still need to validate the HTMl here. We may be on a redirect.
            if (Helper::validateHtml($this->app->getBody())) {
                $this->pageCache->store($this->app->getBody());
            }
        }
    }

    /**
     * If Page Cache plugin is already disabled then this will disable the Page Cache object when it is constructed
     *
     * @return bool
     */
    public function onPageCacheSetCaching(): bool
    {
        return $this->enabled;
    }

    public function onPageCacheGetKey()
    {
        return Factory::getLanguage()->getTag();
    }

    private function addUpdateFormTokenAjax($html)
    {
        $url = SystemUri::baseFull() . 'index.php?option=com_ajax&format=json&plugin=getformtoken';

        /** @see plgSystemJchoptimizepagecache::onAjaxGetformtoken() */
        $script = <<<JS
const updateFormToken = async() => {
    const response = await fetch('$url');
    
    if (response.ok) {
        const jsonValue = await response.json();
            
        return Promise.resolve(jsonValue);
    }
}

updateFormToken().then(data => {
    const formRegex = new RegExp('[0-9a-f]{32}');
    
    for (let formToken of document.querySelectorAll('input[type=hidden]')){
        if (formToken.value == '1' && formRegex.test(formToken.name)){
            formToken.name = data.data[0];
        }
    }
    
    const jsonRegex = new RegExp('"csrf\.token":"[^"]+"');
    
    for(let scriptToken of document.querySelectorAll('script[type="application/json"]')){
        if(scriptToken.classList.contains('joomla-script-options')){
            let json = scriptToken.textContent;
            if(jsonRegex.test(json)){
                scriptToken.textContent = json.replace(jsonRegex, '"csrf.token":"' + data.data[0] + '"');
            }
        }
    }
});
JS;
        $htmlScript = '<script>' . Js::optimize($script) . '</script>';

        $html = str_replace('</body>', $htmlScript . "\n" . '</body>', $html);

        return $html;
    }

    public function onAjaxGetformtoken(): string
    {
        return Session::getFormToken();
    }
}
