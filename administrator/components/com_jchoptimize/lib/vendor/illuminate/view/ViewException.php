<?php

namespace _JchOptimizeVendor\Illuminate\View;

use _JchOptimizeVendor\Illuminate\Container\Container;
use _JchOptimizeVendor\Illuminate\Support\Reflector;

class ViewException extends \ErrorException
{
    /**
     * Report the exception.
     *
     * @return null|bool
     */
    public function report()
    {
        $exception = $this->getPrevious();
        if (Reflector::isCallable($reportCallable = [$exception, 'report'])) {
            return Container::getInstance()->call($reportCallable);
        }

        return \false;
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        $exception = $this->getPrevious();
        if ($exception && \method_exists($exception, 'render')) {
            return $exception->render($request);
        }
    }
}
