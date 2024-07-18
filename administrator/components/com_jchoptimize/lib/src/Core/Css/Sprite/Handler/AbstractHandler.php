<?php

/**
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace JchOptimize\Core\Css\Sprite\Handler;

use JchOptimize\Core\Css\Sprite\HandlerInterface;
use Joomla\Registry\Registry;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

\defined('_JCH_EXEC') or exit('Restricted access');
abstract class AbstractHandler implements HandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public array $spriteFormats = [];

    protected Registry $params;

    protected array $options;

    public function __construct(Registry $params, array $options)
    {
        $this->params = $params;
        $this->options = $options;
    }
}
