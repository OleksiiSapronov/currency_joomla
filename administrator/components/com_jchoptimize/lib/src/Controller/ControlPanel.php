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

namespace JchOptimize\Controller;

use _JchOptimizeVendor\Joomla\Controller\AbstractController;
use _JchOptimizeVendor\Joomla\DI\ContainerAwareInterface;
use _JchOptimizeVendor\Joomla\DI\ContainerAwareTrait;
use JchOptimize\Core\Admin\Icons;
use JchOptimize\Core\Cdn;
use JchOptimize\Core\PageCache\CaptureCache;
use JchOptimize\Joomla\Plugin\PluginHelper;
use JchOptimize\Model\Updates;
use JchOptimize\View\ControlPanelHtml;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Input\Input;

\defined('_JEXEC') or exit('Restricted Access');
class ControlPanel extends AbstractController implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    private ControlPanelHtml $view;

    private Updates $updatesModel;

    private Icons $icons;
    private Cdn $cdn;

    /**
     * Constructor.
     */
    public function __construct(Updates $updatesModel, ControlPanelHtml $view, Icons $icons, Cdn $cdn, Input $input = null, AdministratorApplication $app = null)
    {
        $this->updatesModel = $updatesModel;
        $this->view = $view;
        $this->icons = $icons;
        $this->cdn = $cdn;
        parent::__construct($input, $app);
    }

    public function execute(): bool
    {
        $this->manageUpdates();
        if (\JCH_PRO) {
            /** @var CaptureCache $captureCache */
            $captureCache = $this->container->get(CaptureCache::class);
            $captureCache->updateHtaccess();
        }
        $this->cdn->updateHtaccess();
        $this->view->setData(['view' => 'ControlPanel', 'icons' => $this->icons]);
        $this->view->loadResources();
        $this->view->loadToolBar();
        if (!PluginHelper::isEnabled('system', 'jchoptimize')) {
            if (\JCH_PRO) {
                $editUrl = Route::_('index.php?option=com_jchoptimize&view=ModeSwitcher&task=setProduction&return='.\base64_encode((string) Uri::getInstance()), \false);
            } else {
                $editUrl = Route::_('index.php?option=com_plugins&filter[search]=JCH Optimize&filter[folder]=system');
            }

            /** @var AdministratorApplication $app */
            $app = $this->getApplication();
            $app->enqueueMessage(Text::sprintf('COM_JCHOPTIMIZE_PLUGIN_NOT_ENABLED', $editUrl), 'warning');
        }
        echo $this->view->render();

        return \true;
    }

    private function manageUpdates(): void
    {
        $this->updatesModel->upgradeLicenseKey();
        $this->updatesModel->refreshUpdateSite();
        $this->updatesModel->removeObsoleteUpdateSites();
        if (\JCH_PRO) {
            if ('' == $this->updatesModel->getLicenseKey()) {
                if (\version_compare(JVERSION, '4.0', 'lt')) {
                    $dlidEditUrl = Route::_('index.php?option=com_config&view=component&component=com_jchoptimize');
                } else {
                    $dlidEditUrl = Route::_('index.php?option=com_installer&view=updatesites&filter[search]=JCH Optimize&filter[supported]=1');
                }

                /** @var AdministratorApplication $app */
                $app = $this->getApplication();
                $app->enqueueMessage(Text::sprintf('COM_JCHOPTIMIZE_DOWNLOADID_MISSING', $dlidEditUrl), 'warning');
            }
        }
    }
}
