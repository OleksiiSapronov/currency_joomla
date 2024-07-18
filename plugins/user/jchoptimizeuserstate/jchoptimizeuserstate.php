<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

use JchOptimize\ContainerFactory;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

defined( '_JEXEC' ) or die ( 'Restricted access' );

include_once JPATH_ADMINISTRATOR . '/components/com_jchoptimize/autoload.php';

class plgUserJchoptimizeuserstate extends CMSPlugin
{
	/**
	 * @var CMSApplication
	 */
	protected $app;
	/**
	 * @var Input
	 */
	protected $input;
	/**
	 * @var Registry
	 */
	protected $comParams;

	public function __construct( &$subject, $config = array() )
	{
		parent::__construct( $subject, $config );

		$container       = ContainerFactory::getContainer();
		$this->input     = $container->get( Input::class );
		$this->comParams = $container->get( 'params' );
	}

	public function onUserAfterLogin( $options = [] )
	{
		if ( $this->app->isClient( 'site' ) )
		{
			$options = [
				'expires'  => 0,
				'path'     => $this->app->get( 'cookie_path', '/' ),
				'domain'   => $this->app->get( 'cookie_domain', '' ),
				'secure'   => $this->app->isHttpsForced(),
				'httponly' => true,
				'samesite' => 'Lax'
			];

			$this->input->cookie->set( 'jch_optimize_no_cache_user_state', 'user_logged_in', $options );
		}
	}

	public function onUserAfterLogout( $options = [] )
	{
		if ( $this->app->isClient( 'site' ) )
		{
			$options = [
				'expires'  => 1,
				'path'     => $this->app->get( 'cookie_path', '/' ),
				'domain'   => $this->app->get( 'cookie_domain', '' ),
				'secure'   => $this->app->isHttpsForced(),
				'httponly' => true,
				'samesite' => 'Lax'
			];

			$this->input->cookie->set( 'jch_optimize_no_cache_user_state', '', $options );
		}
	}

	public function onUserPostForm()
	{
		$options = [
			'expires'  => time() + (int)$this->comParams->get( 'page_cache_lifetime', '900' ),
			'path'     => $this->app->get( 'cookie_path', '/' ),
			'domain'   => $this->app->get( 'cookie_domain', '' ),
			'secure'   => $this->app->isHttpsForced(),
			'httponly' => true,
			'samesite' => 'Lax'
		];

		$this->input->cookie->set( 'jch_optimize_no_cache_user_activity', 'user_posted_form', $options );
	}

	public function onUserPostFormDeleteCookie()
	{
		$options = [
			'expires'  => 1,
			'path'     => $this->app->get( 'cookie_path', '/' ),
			'domain'   => $this->app->get( 'cookie_domain', '' ),
			'secure'   => $this->app->isHttpsForced(),
			'httponly' => true,
			'samesite' => 'Lax'
		];

		$this->input->cookie->set( 'jch_optimize_no_cache_user_activity', '', $options );
	}
}
