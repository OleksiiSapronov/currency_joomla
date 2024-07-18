<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

use JchOptimize\ContainerFactory;
use JchOptimize\Core\Container\Container;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Optimize;
use JchOptimize\Core\SystemUri;
use JchOptimize\Model\Cache;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Psr\Log\LoggerInterface;

defined('_JEXEC') or die('Restricted access');

if (!defined('JCH_PLUGIN_DIR')) {
    define('JCH_PLUGIN_DIR', dirname(__FILE__));
}

include_once JPATH_ADMINISTRATOR . '/components/com_jchoptimize/autoload.php';

class plgSystemJchoptimize extends CMSPlugin
{
    /**
     * If plugin is enabled
     *
     * @var bool
     */
    public bool $enabled = true;

    /**
     * Component parameters
     *
     * @var Registry
     */
    protected Registry $comParams;

    /**
     * Application object
     *
     * @var   ?CMSApplicationInterface
     */
    protected $app;

    /**
     * Container object
     *
     * @var Container
     */
    private Container $container;

    /**
     * Constructor
     *
     * @param $subject
     * @param $config
     */
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);

        // Disable if the component is not installed or disabled
        if (!ComponentHelper::isEnabled('com_jchoptimize')) {
            $this->enabled = false;

            return;
        }

        //Disable if we cannot initialize application
        try {
            $this->app = Factory::getApplication();
        } catch (Exception $e) {
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

        //Disable if not on front end
        if (!$this->app->isClient('site')) {
            $this->enabled = false;

            return;
        }

        //Disable if jchnooptimize set
        if ($this->app->input->get('jchnooptimize', '', 'int') == 1) {
            $this->enabled = false;

            return;
        }

        //Disable if site offline and user is guest
        $user = Factory::getUser();

        if ($this->app->get('offline', '0') && $user->get('guest')) {
            $this->enabled = false;

            return;
        }

        //Get and set component's parameters
        $this->comParams = $this->container->get('params');

        if (!defined('JCH_DEBUG')) {
            define('JCH_DEBUG', ($this->comParams->get('debug', 0) && JDEBUG));
        }

        if ($this->comParams->get('disable_logged_in_users', '1') && !$user->get('guest')) {
            $this->enabled = false;
        }
    }

    public function onAfterRoute()
    {
        //If already disabled return
        if (!$this->enabled) {
            return;
        }
        //Disable if in iframe
        if ($this->app->input->server->getString('HTTP_SEC_FETCH_DEST') == 'iframe') {
            $this->enabled = false;

            return;
        }

        //Disable if menu or page excluded
        $menuexcluded = $this->comParams->get('menuexcluded', array());
        $menuexcludedurl = $this->comParams->get('menuexcludedurl', array());

        if (in_array($this->app->input->get('Itemid', '', 'int'), $menuexcluded) ||
            Helper::findExcludes($menuexcludedurl, SystemUri::toString())) {
            $this->enabled = false;

            return;
        }

        //Disable if page being edited
        if ($this->app->input->get('layout') == 'edit') {
            $this->enabled = false;

            return;
        }
    }

    /**
     *
     * @return boolean
     * @throws Exception
     */
    public function onAfterRender()
    {
        if (!$this->enabled) {
            return false;
        }

        if ($this->comParams->get('debug', 0)) {
            error_reporting(E_ALL & ~E_NOTICE);
        }

        $html = $this->app->getBody();

        //Html invalid
        if (!Helper::validateHtml($html)) {
            return false;
        }

        if ($this->app->input->get('jchbackend') == '1'
            || $this->app->input->get('jchnooptimize' == '1')) {
            return false;
        }

        if ($this->app->input->get('jchbackend') == '2') {
            echo $html;
            while (@ob_end_flush()) {
                ;
            }
            exit;
        }

        try {
            /** @var Optimize $optimize */
            $optimize = $this->container->get(Optimize::class);
            $optimize->setHtml($html);

            $sOptimizedHtml = $optimize->process();
        } catch (Exception $e) {
            $logger = $this->container->get(LoggerInterface::class);
            $logger->error($e->getMessage());

            $sOptimizedHtml = $html;
        }

        $this->app->setBody($sOptimizedHtml);
    }

    public function onJchCacheExpired()
    {
        /** @var Cache $cacheModel
         */
        $cacheModel = $this->container->get(Cache::class);

        return $cacheModel->cleanCache();
    }

    /**
     *
     */
    public function onAfterDispatch()
    {
        //If already disabled return false
        if (!$this->enabled) {
            return false;
        }

        //Disable if editor loaded
        if ($this->isEditorLoaded()) {
            $this->enabled = false;

            return false;
        }
    }

    /**
     * Gets the name of the current Editor
     *
     * @staticvar string $sEditor
     * @return bool
     */
    protected function isEditorLoaded(): bool
    {
        $aEditors = PluginHelper::getPlugin('editors');

        foreach ($aEditors as $sEditor) {
            if (class_exists('plgEditor' . $sEditor->name, false)) {
                return true;
            }
        }

        return false;
    }
}
