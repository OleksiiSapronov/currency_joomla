<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads.
 *
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Controller;

use _JchOptimizeVendor\Joomla\Controller\AbstractController;
use _JchOptimizeVendor\Joomla\DI\ContainerAwareInterface;
use _JchOptimizeVendor\Joomla\DI\ContainerAwareTrait;
use _JchOptimizeVendor\Laminas\Paginator\Adapter\ArrayAdapter;
use _JchOptimizeVendor\Laminas\Paginator\Paginator;
use JchOptimize\Joomla\Plugin\PluginHelper;
use JchOptimize\Model\ModeSwitcher;
use JchOptimize\Model\PageCache as PageCacheModel;
use JchOptimize\Model\ReCache;
use JchOptimize\View\PageCacheHtml;
use Joomla\Application\AbstractApplication;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Input\Input;

\defined('_JEXEC') or exit('Restricted Access');
class PageCache extends AbstractController implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    private PageCacheHtml $view;
    private PageCacheModel $pageCacheModel;

    public function __construct(PageCacheModel $pageCacheModel, PageCacheHtml $view, ?Input $input = null, ?AbstractApplication $app = null)
    {
        $this->pageCacheModel = $pageCacheModel;
        $this->view = $view;
        parent::__construct($input, $app);
    }

    public function execute()
    {
        /** @var Input $input */
        $input = $this->getInput();

        /** @var AdministratorApplication $app */
        $app = $this->getApplication();
        if ('remove' == $input->get('task')) {
            $success = $this->pageCacheModel->delete((array) $input->get('cid', []));
        }
        if ('deleteAll' == $input->get('task')) {
            $success = $this->pageCacheModel->deleteAll();
        }
        if (\JCH_PRO && 'recache' == $input->get('task')) {
            /** @var ReCache $reCacheModel */
            $reCacheModel = $this->container->get(ReCache::class);
            $redirectUrl = Route::_('index.php?option=com_jchoptimize&view=PageCache', \false, 0, \true);
            $reCacheModel->reCache($redirectUrl);
        }
        if (isset($success)) {
            if ($success) {
                $message = Text::_('COM_JCHOPTIMIZE_PAGECACHE_DELETED_SUCCESSFULLY');
                $messageType = 'success';
            } else {
                $message = Text::_('COM_JCHOPTIMIZE_PAGECACHE_DELETE_ERROR');
                $messageType = 'error';
            }
            $app->enqueueMessage($message, $messageType);
            $app->redirect(Route::_('index.php?option=com_jchoptimize&view=PageCache', \false));
        }
        $integratedPageCache = 'jchoptimizepagecache';
        if (\JCH_PRO) {
            /** @var ModeSwitcher $modeSwitcher */
            $modeSwitcher = $this->container->get(ModeSwitcher::class);
            $integratedPageCache = $modeSwitcher->getIntegratedPageCachePlugin();
        }
        if ('jchoptimizepagecache' == $integratedPageCache) {
            if (!PluginHelper::isEnabled('system', 'jchoptimizepagecache')) {
                if (\JCH_PRO === '1') {
                    $editUrl = Route::_('index.php?option=com_jchoptimize&view=Utility&task=togglepagecache&return='.\base64_encode((string) Uri::getInstance()), \false);
                } else {
                    $editUrl = Route::_('index.php?option=com_plugins&filter[search]=JCH Optimize Page Cache&filter[folder]=system');
                }
                $app->enqueueMessage(Text::sprintf('COM_JCHOPTIMIZE_PAGECACHE_NOT_ENABLED', $editUrl), 'warning');
            }
        } elseif (\JCH_PRO) {
            /** @var ModeSwitcher $modeSwitcher */
            $modeSwitcher = $this->container->get(ModeSwitcher::class);
            $app->enqueueMessage(Text::sprintf('COM_JCHOPTIMIZE_INTEGRATED_PAGE_CACHE_NOT_JCHOPTIMIZE', Text::_($modeSwitcher->pageCachePlugins[$integratedPageCache])), 'info');
        }

        /** @var int $defaultListLimit */
        $defaultListLimit = $app->get('list_limit');
        $paginator = new Paginator(new ArrayAdapter($this->pageCacheModel->getItems()));
        $paginator->setCurrentPageNumber((int) $input->get('list_page', '1'))->setItemCountPerPage((int) $this->pageCacheModel->getState()->get('list_limit', $defaultListLimit));
        $this->view->setData(['items' => $paginator, 'view' => 'PageCache', 'paginator' => $paginator->getPages(), 'pageLink' => 'index.php?option=com_jchoptimize&view=PageCache', 'adapter' => $this->pageCacheModel->getAdaptorName(), 'httpRequest' => $this->pageCacheModel->isCaptureCacheEnabled()]);
        $this->view->renderStatefulElements($this->pageCacheModel->getState());
        $this->view->loadResources();
        $this->view->loadToolBar();
        echo $this->view->render();

        return \true;
    }
}
