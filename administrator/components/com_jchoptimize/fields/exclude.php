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

defined('_JEXEC') or die;

use JchOptimize\ContainerFactory;
use JchOptimize\Core\Admin\MultiSelectItems;
use JchOptimize\Core\Helper;
use Joomla\CMS\Uri\Uri as JUri;

include_once JPATH_ADMINISTRATOR . '/components/com_jchoptimize/autoload.php';
include_once JPATH_ADMINISTRATOR . '/components/com_jchoptimize/version.php';

JFormHelper::loadFieldClass('textarea');

abstract class JFormFieldExclude extends JFormFieldTextarea
{

    protected bool $first_field = false;
    protected string $filegroup = 'file';
    protected string $filetype = '';
    /**
     * @var MultiSelectItems
     */
    protected $multiSelect;

    public function __construct($form = null)
    {
        parent::__construct($form);

        $container = ContainerFactory::getContainer();
        $this->multiSelect = $container->buildObject(MultiSelectItems::class);
    }

    public function setup(SimpleXMLElement $element, $value, $group = null): bool
    {
        $value = $this->castValue($value);

        return parent::setup($element, $value, $group);
    }

    /**
     *
     * @param   string  $value
     *
     * @return array
     */
    protected function castValue($value)
    {
        if ( ! is_array($value)) {
            $value = Helper::getArray($value);
        }

        return $value;
    }

    /**
     *
     * @return string
     */
    protected function getInput()
    {
        $attributes = 'class="inputbox chzn-custom-value input-xlarge jch-multiselect" multiple size="5" data-jch_type="' . $this->filetype . '" data-jch_param="' . $this->fieldname . '" data-jch_group="' . $this->filegroup . '"';
        $select = JHTML::_(
                'select.genericlist',
                $this->getOptions(),
                'jform[' . $this->fieldname . '][]',
                $attributes,
                'id',
                'name',
                $this->value,
                $this->id
        );
        $uriRoot = JUri::root();

        return <<<HTML
<div id="div-{$this->fieldname}">{$select}
	<img id="img-{$this->fieldname}" class="jch-multiselect-loading-image" src="{$uriRoot}media/com_jchoptimize/core/images/exclude-loader.gif" />
        <button type="button" class="btn btn-sm btn-secondary jch-multiselect-add-button" onmousedown="jchMultiselect.addJchOption('jform_{$this->fieldname}')" style="display: none;">Add item</button>
</div>
HTML;
    }

    protected function getOptions(): array
    {
        $options = [];

        foreach ($this->value as $excludeValue) {
            $options[$excludeValue] = $this->multiSelect->{'prepare' . ucfirst($this->filegroup) . 'Values'}(
                    $excludeValue
            );
        }

        return $options;
    }
}
