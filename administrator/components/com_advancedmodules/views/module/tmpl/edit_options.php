<?php
/**
 * @package         Advanced Module Manager
 * @version         9.9.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

/**
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;

?>
<?php
echo JHtml::_('bootstrap.startAccordion', 'moduleOptions', ['active' => 'collapse0']);
$fieldSets = $this->form->getFieldsets('params');
$i         = 0;

foreach ($fieldSets as $name => $fieldSet) :
    $label = ($fieldSet->label ?? null) ?: 'COM_MODULES_' . $name . '_FIELDSET_LABEL';
    $class = $fieldSet->class ?? '';

    echo JHtml::_('bootstrap.addSlide', 'moduleOptions', JText::_($label), 'collapse' . ($i++), $class);

    if (isset($fieldSet->description) && trim($fieldSet->description)) :
        echo '<p class="tip">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
    endif;
    ?>
    <?php foreach ($this->form->getFieldset($name) as $field) : ?>
    <div class="control-group">
        <div class="control-label">
            <?php echo $field->label; ?>
        </div>
        <div class="controls">
            <?php echo $field->input; ?>
        </div>
    </div>
<?php endforeach;
    echo JHtml::_('bootstrap.endSlide');
endforeach;
echo JHtml::_('bootstrap.endAccordion');
