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
use JchOptimize\Core\Admin\Icons;
use JchOptimize\Model\ApiParams;
use JchOptimize\View\OptimizeImagesHtml;

\defined('_JEXEC') or exit('Restricted Access');
class OptimizeImages extends AbstractController
{
    private OptimizeImagesHtml $view;

    private ApiParams $model;

    private Icons $icons;

    /**
     * Constructor.
     */
    public function __construct(ApiParams $model, OptimizeImagesHtml $view, Icons $icons)
    {
        $this->model = $model;
        $this->view = $view;
        $this->icons = $icons;
        parent::__construct();
    }

    public function execute(): bool
    {
        $this->view->setData(['view' => 'OptimizeImages', 'apiParams' => \json_encode($this->model->getCompParams()), 'icons' => $this->icons]);
        $this->view->loadResources();
        $this->view->loadToolBar();
        echo $this->view->render();

        return \true;
    }
}
