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

use JchOptimize\ContainerFactory;
use Joomla\Filesystem\Folder;

defined( '_JEXEC' ) or die;

include_once dirname( __FILE__ ) . '/exclude.php';

class JFormFieldExcludecomponent extends JFormFieldExclude
{
	public $type = 'excludecomponent';

	protected function getOptions(): array
	{
		$options = [];

		$params = ContainerFactory::getContainer()->get( 'params' );

		$installedComponents = Folder::folders( JPATH_SITE . '/components' );
		$excludedComponents  = $params->get( 'cache_exclude_component', [ 'com_ajax' ] );

		$components = array_unique( array_merge( $installedComponents, $excludedComponents ) );

		foreach ( $components as $component )
		{
			$options[ $component ] = $component;
		}

		return $options;
	}
}