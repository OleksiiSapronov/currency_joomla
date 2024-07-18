<?php
/**
 * Button Helper class: com_zoo.item
 *
 * @package         Better Preview
 * @version         3.4.2
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2015 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

class helperBetterPreviewButtonZooitem extends helperBetterPreviewButton
{
	function getExtraJavaScript($text)
	{
		return '
				isjform = 0;
			';
	}

	function getURL($name)
	{
		$id = JFactory::getApplication()->input->get('cid', array(0), 'array');
		$id = (int) $id[0];

		if (!$id)
		{
			return;
		}

		require_once(JPATH_ADMINISTRATOR . '/components/com_zoo/config.php');

		$zoo = App::getInstance('zoo');

		$item = $zoo->table->item->get($id);

		return $zoo->route->item($item, 0) . '&item_id=' . $id;
	}
}
