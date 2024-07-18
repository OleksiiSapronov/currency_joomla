<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads.
 *
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2023 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Model;

use _JchOptimizeVendor\Joomla\Model\DatabaseModelInterface;
use _JchOptimizeVendor\Joomla\Model\DatabaseModelTrait;
use _JchOptimizeVendor\Joomla\Model\StatefulModelInterface;
use _JchOptimizeVendor\Joomla\Model\StatefulModelTrait;
use _JchOptimizeVendor\Psr\Http\Message\UploadedFileInterface;
use JchOptimize\Core\Exception\ExceptionInterface;
use JchOptimize\Core\SystemUri;
use Joomla\Filesystem\File;
use Joomla\Registry\Registry;

class BulkSettings implements DatabaseModelInterface, StatefulModelInterface
{
    use DatabaseModelTrait;
    use StatefulModelTrait;
    use \JchOptimize\Model\SaveSettingsTrait;

    public function __construct(Registry $params)
    {
        $this->setState($params);
        $this->name = 'buk_settings';
    }

    /**
     * @throws ExceptionInterface
     */
    public function importSettings(UploadedFileInterface $file): void
    {
        $tmpDir = \JPATH_ROOT.'/tmp';
        $fileName = $file->getClientFilename() ?? \tempnam($tmpDir, 'jchoptimize_');
        $targetPath = $tmpDir.'/'.$fileName;
        // if file not already at target path move it
        if (!\file_exists($targetPath)) {
            $file->moveTo($targetPath);
        }
        $params = (new Registry())->loadFile($targetPath);
        File::delete($targetPath);
        $this->setState($params);
        $this->saveSettings();
    }

    public function exportSettings(): string
    {
        $file = \JPATH_SITE.'/tmp/'.SystemUri::currentUri()->getHost().'_jchoptimize_settings.json';
        $params = $this->state->toString();
        File::write($file, $params);

        return $file;
    }

    /**
     * @throws ExceptionInterface
     */
    public function setDefaultSettings(): void
    {
        $this->setState(new Registry([]));
        $this->saveSettings();
    }
}
