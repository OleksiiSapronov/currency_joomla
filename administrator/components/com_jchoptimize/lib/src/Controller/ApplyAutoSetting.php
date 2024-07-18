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
use JchOptimize\Core\Exception\ExceptionInterface;
use JchOptimize\Model\Configure;
use Joomla\Application\AbstractApplication;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\Input\Input;

\defined('_JEXEC') or exit('Restricted Access');
class ApplyAutoSetting extends AbstractController
{
    private Configure $model;

    public function __construct(Configure $model, ?Input $input = null, ?AbstractApplication $app = null)
    {
        $this->model = $model;
        parent::__construct($input, $app);
    }

    public function execute(): bool
    {
        /** @var Input $input */
        $input = $this->getInput();

        /** @var AdministratorApplication $app */
        $app = $this->getApplication();

        try {
            $this->model->applyAutoSettings((string) $input->get('autosetting', 's1'));
        } catch (ExceptionInterface $e) {
        }
        $body = \json_encode(['success' => \true]);
        $app->clearHeaders();
        $app->setHeader('Content-Type', 'application/json');
        $app->setHeader('Content-Length', (string) \strlen($body));
        $app->setBody($body);
        $app->allowCache(\false);
        echo $app->toString();
        $app->close();

        return \true;
    }
}
