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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

include_once dirname(__FILE__) . '/exclude.php';

class JFormFieldExcludejspei extends JFormFieldExclude
{

    public $type = 'excludejspei';
    public string $filetype = 'js';
    public string $filegroup = 'file';
    protected string $valueType = 'url';

    protected function getInput(): string
    {
        $this->value = array_values($this->value);
        $i = 0;
        $nextIndex = count($this->value);

        $field = <<<HTML
<fieldset id="fieldset-{$this->fieldname}" data-index="{$nextIndex}">
<div class="jch-js-fieldset-children jch-js-excludes-header">
<span class="jch-js-ieo-header">&nbsp;&nbsp;Ignore execution order&nbsp;&nbsp;&nbsp;</span>
<span class="jch-js-dontmove-header">&nbsp;&nbsp;&nbsp;Don't move to bottom&nbsp;&nbsp;</span>
</div>
HTML;
        foreach ($this->value as $value) {
            //Sanity check
            if (!isset($value[$this->valueType])) {
                continue;
            }

            $ieoChecked = isset($value['ieo']) ? 'checked' : '';
            $dontMoveChecked = isset($value['dontmove']) ? 'checked' : '';
            $dataValue = $this->multiSelect->{'prepare' . ucfirst($this->filegroup) . 'Values'}(
                $value[$this->valueType]
            );
            $size = strlen($value[$this->valueType]);

            $field .= <<<HTML
<div id="div-{$this->fieldname}-{$i}" class="jch-js-fieldset-children jch-js-excludes-container">
<span class="jch-js-excludes"><span><input type="text" readonly size="{$size}" value="{$value[$this->valueType]}" name="jform[{$this->fieldname}][$i][{$this->valueType}]"> 
{$dataValue}
<button type="button" class="jch-multiselect-remove-button" onmouseup="jchMultiselect.removeJchJsOption('div-{$this->fieldname}-{$i}', 'jform_{$this->fieldname}')"></button>
</span></span>
<span class="jch-js-ieo">
    <input type="checkbox" name="jform[{$this->fieldname}][$i][ieo]" {$ieoChecked}/>
</span>
<span class="jch-js-dontmove">
    <input type="checkbox" name="jform[{$this->fieldname}][$i][dontmove]" {$dontMoveChecked} />
</span>
</div>
HTML;
            $i++;
        }
        $attributes = 'class="inputbox chzn-custom-value input-large jch-multiselect"  multiple data-jch_type="' . $this->filetype . '" data-jch_param="' . $this->fieldname . '" data-jch_group="' . $this->filegroup . '"';
        $select = HTMLHelper::_(
            'select.genericlist',
            $this->getOptions(),
            'jform[' . $this->fieldname . '][][' . $this->valueType . ']',
            $attributes,
            'id',
            'name',
            $this->value,
            $this->id
        );

        $uriRoot = Uri::root();

        $field .= <<<HTML
</fieldset>
<div id="div-{$this->fieldname}">{$select}
    <img id="img-{$this->fieldname}" class="jch-multiselect-loading-image" src="{$uriRoot}media/com_jchoptimize/core/images/exclude-loader.gif" />
    <button type="button" class="btn btn-sm btn-secondary jch-multiselect-add-button" onmousedown="jchMultiselect.addJchJsOption('jform_{$this->fieldname}', '{$this->fieldname}', '{$this->valueType}')" style="display: none;">Add item</button>
</div>
<script>
jQuery('#jform_{$this->fieldname}').on('change', function(evt, params){
    jchMultiselect.appendJchJsOption('jform_{$this->fieldname}', '{$this->fieldname}',  params, '{$this->valueType}');
});
</script>
HTML;

        return $field;
    }

    protected function getOptions(): array
    {
        return [];
    }
}
