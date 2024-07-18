<?php

namespace _JchOptimizeVendor\Illuminate\View\Compilers\Concerns;

use _JchOptimizeVendor\Illuminate\Support\Js;

trait CompilesJs
{
    /**
     * Compile the "@js" directive into valid PHP.
     *
     * @return string
     */
    protected function compileJs(string $expression)
    {
        return \sprintf('<?php echo \\%s::from(%s)->toHtml() ?>', Js::class, $this->stripParentheses($expression));
    }
}
