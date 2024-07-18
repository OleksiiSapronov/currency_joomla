<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 *  @package   jchoptimize/core
 *  @author    Samuel Marshall <samuel@jch-optimize.net>
 *  @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 *  @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

use JchOptimize\Core\Admin\Helper;

defined( '_JEXEC' ) or die;

include_once dirname( __FILE__ ) . '/exclude.php';

class JFormFieldCssbgimages extends JFormFieldExclude
{
	public $type = 'cssbgimages';
	public string $filetype = 'css';
	public string $filegroup = 'class';

	protected function getInput()
	{
		if ( ! JCH_PRO )
		{
			return Helper::proOnlyField();
		}
		else
		{
			return parent::getInput();
		}
	}
}