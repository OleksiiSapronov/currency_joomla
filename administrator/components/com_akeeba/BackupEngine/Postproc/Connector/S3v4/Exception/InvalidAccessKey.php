<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Postproc\Connector\S3v4\Exception;

defined('AKEEBAENGINE') || die();

use Exception;

/**
 * Invalid Amazon S3 access key
 */
class InvalidAccessKey extends ConfigurationError
{
	public function __construct($message = "", $code = 0, Exception $previous = null)
	{
		if (empty($message))
		{
			$message = 'The Amazon S3 Access Key provided is invalid';
		}

		parent::__construct($message, $code, $previous);
	}

}
