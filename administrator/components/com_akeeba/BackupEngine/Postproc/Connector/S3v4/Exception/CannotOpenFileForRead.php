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
use RuntimeException;

class CannotOpenFileForRead extends RuntimeException
{
	public function __construct($file = "", $code = 0, Exception $previous = null)
	{
		$message = "Cannot open $file for reading";

		parent::__construct($message, $code, $previous);
	}

}
