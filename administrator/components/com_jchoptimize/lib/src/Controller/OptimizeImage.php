<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads.
 *
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2021 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Controller;

use _JchOptimizeVendor\Joomla\Controller\AbstractController;
use JchOptimize\Core\Admin\Ajax\Ajax as AdminAjax;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Router\Route as JRoute;
use Joomla\Input\Input;

\defined('_JEXEC') or exit('Restricted Access');
class OptimizeImage extends AbstractController
{
    public function execute(): bool
    {
        /** @var Input $input */
        $input = $this->getInput();

        /** @var null|string $status */
        $status = $input->get('status', null);

        /** @var AdministratorApplication $app */
        $app = $this->getApplication();
        if (\is_null($status)) {
            echo AdminAjax::getInstance('OptimizeImage')->run();
            $app->close();
        } else {
            if ('success' == $status) {
                $dir = \rtrim((string) $input->get('dir', ''), '/').'/';
                $cnt = (int) $input->get('cnt', 0);
                $app->enqueueMessage(\sprintf(JText::_('%1$d images optimized in %2$s'), $cnt, $dir));
            } else {
                $msg = (string) $input->get('msg', '');
                $app->enqueueMessage(JText::_('The Optimize Image function failed with message "'.$msg), 'error');
            }
            $app->redirect(JRoute::_('index.php?option=com_jchoptimize&view=OptimizeImages', \false));
        }

        return \true;
    }
}
