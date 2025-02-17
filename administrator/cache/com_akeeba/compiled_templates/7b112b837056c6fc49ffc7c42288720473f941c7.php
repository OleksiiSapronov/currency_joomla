<?php /* /home/1255990.cloudwaysapps.com/uwxwemhmnh/public_html/administrator/components/com_akeeba/ViewTemplates/ControlPanel/warning_phpversion.blade.php */ ?>
<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var $this \Akeeba\Backup\Admin\View\ControlPanel\Html */

// Protect from unauthorized access
defined('_JEXEC') || die();

use FOF30\Date\Date;

?>
<?php /* Old PHP version reminder */ ?>
<?php echo $this->loadAnyTemplate('admin:com_akeeba/CommonTemplates/phpversion_warning', [
    'softwareName'  => 'Akeeba Backup',
    'minPHPVersion' => '7.1.0',
]); ?>
