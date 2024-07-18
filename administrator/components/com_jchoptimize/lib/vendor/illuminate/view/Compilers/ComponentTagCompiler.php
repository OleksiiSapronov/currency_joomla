<?php

namespace _JchOptimizeVendor\Illuminate\View\Compilers;

use _JchOptimizeVendor\Illuminate\Container\Container;
use _JchOptimizeVendor\Illuminate\Contracts\Foundation\Application;
use _JchOptimizeVendor\Illuminate\Contracts\View\Factory;
use _JchOptimizeVendor\Illuminate\Filesystem\Filesystem;
use _JchOptimizeVendor\Illuminate\Support\Str;
use _JchOptimizeVendor\Illuminate\View\AnonymousComponent;
use _JchOptimizeVendor\Illuminate\View\DynamicComponent;
use _JchOptimizeVendor\Illuminate\View\ViewFinderInterface;

/**
 * @author Spatie bvba <info@spatie.be>
 * @author Taylor Otwell <taylor@laravel.com>
 */
class ComponentTagCompiler
{
    /**
     * The Blade compiler instance.
     *
     * @var \Illuminate\View\Compilers\BladeCompiler
     */
    protected $blade;

    /**
     * The component class aliases.
     *
     * @var array
     */
    protected $aliases = [];

    /**
     * The component class namespaces.
     *
     * @var array
     */
    protected $namespaces = [];

    /**
     * The "bind:" attributes that have been compiled for the current component.
     *
     * @var array
     */
    protected $boundAttributes = [];

    /**
     * Create a new component tag compiler.
     *
     * @param null|\Illuminate\View\Compilers\BladeCompiler $blade
     */
    public function __construct(array $aliases = [], array $namespaces = [], ?BladeCompiler $blade = null)
    {
        $this->aliases = $aliases;
        $this->namespaces = $namespaces;
        $this->blade = $blade ?: new BladeCompiler(new Filesystem(), \sys_get_temp_dir());
    }

    /**
     * Compile the component and slot tags within the given string.
     *
     * @return string
     */
    public function compile(string $value)
    {
        $value = $this->compileSlots($value);

        return $this->compileTags($value);
    }

    /**
     * Compile the tags within the given string.
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function compileTags(string $value)
    {
        $value = $this->compileSelfClosingTags($value);
        $value = $this->compileOpeningTags($value);

        return $this->compileClosingTags($value);
    }

    /**
     * Get the component class for a given component alias.
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function componentClass(string $component)
    {
        $viewFactory = Container::getInstance()->make(Factory::class);
        if (isset($this->aliases[$component])) {
            if (\class_exists($alias = $this->aliases[$component])) {
                return $alias;
            }
            if ($viewFactory->exists($alias)) {
                return $alias;
            }

            throw new \InvalidArgumentException("Unable to locate class or view [{$alias}] for component [{$component}].");
        }
        if ($class = $this->findClassByComponent($component)) {
            return $class;
        }
        if (\class_exists($class = $this->guessClassName($component))) {
            return $class;
        }
        if ($viewFactory->exists($view = $this->guessViewName($component))) {
            return $view;
        }
        if ($viewFactory->exists($view = $this->guessViewName($component).'.index')) {
            return $view;
        }

        throw new \InvalidArgumentException("Unable to locate a class or view for component [{$component}].");
    }

    /**
     * Find the class for the given component using the registered namespaces.
     *
     * @return null|string
     */
    public function findClassByComponent(string $component)
    {
        $segments = \explode('::', $component);
        $prefix = $segments[0];
        if (!isset($this->namespaces[$prefix]) || !isset($segments[1])) {
            return;
        }
        if (\class_exists($class = $this->namespaces[$prefix].'\\'.$this->formatClassName($segments[1]))) {
            return $class;
        }
    }

    /**
     * Guess the class name for the given component.
     *
     * @return string
     */
    public function guessClassName(string $component)
    {
        $namespace = Container::getInstance()->make(Application::class)->getNamespace();
        $class = $this->formatClassName($component);

        return $namespace.'View\\Components\\'.$class;
    }

    /**
     * Format the class name for the given component.
     *
     * @return string
     */
    public function formatClassName(string $component)
    {
        $componentPieces = \array_map(function ($componentPiece) {
            return \ucfirst(Str::camel($componentPiece));
        }, \explode('.', $component));

        return \implode('\\', $componentPieces);
    }

    /**
     * Guess the view name for the given component.
     *
     * @param string $name
     *
     * @return string
     */
    public function guessViewName($name)
    {
        $prefix = 'components.';
        $delimiter = ViewFinderInterface::HINT_PATH_DELIMITER;
        if (Str::contains($name, $delimiter)) {
            return Str::replaceFirst($delimiter, $delimiter.$prefix, $name);
        }

        return $prefix.$name;
    }

    /**
     * Partition the data and extra attributes from the given array of attributes.
     *
     * @param string $class
     *
     * @return array
     */
    public function partitionDataAndAttributes($class, array $attributes)
    {
        // If the class doesn't exists, we'll assume it's a class-less component and
        // return all of the attributes as both data and attributes since we have
        // now way to partition them. The user can exclude attributes manually.
        if (!\class_exists($class)) {
            return [collect($attributes), collect($attributes)];
        }
        $constructor = (new \ReflectionClass($class))->getConstructor();
        $parameterNames = $constructor ? collect($constructor->getParameters())->map->getName()->all() : [];

        return collect($attributes)->partition(function ($value, $key) use ($parameterNames) {
            return \in_array(Str::camel($key), $parameterNames);
        })->all();
    }

    /**
     * Compile the slot tags within the given string.
     *
     * @return string
     */
    public function compileSlots(string $value)
    {
        $pattern = "/\n            <\n                \\s*\n                x[\\-\\:]slot\n                \\s+\n                (:?)name=(?<name>(\"[^\"]+\"|\\\\'[^\\\\']+\\\\'|[^\\s>]+))\n                (?<attributes>\n                    (?:\n                        \\s+\n                        (?:\n                            (?:\n                                \\{\\{\\s*\\\$attributes(?:[^}]+?)?\\s*\\}\\}\n                            )\n                            |\n                            (?:\n                                [\\w\\-:.@]+\n                                (\n                                    =\n                                    (?:\n                                        \\\"[^\\\"]*\\\"\n                                        |\n                                        \\'[^\\']*\\'\n                                        |\n                                        [^\\'\\\"=<>]+\n                                    )\n                                )?\n                            )\n                        )\n                    )*\n                    \\s*\n                )\n                (?<![\\/=\\-])\n            >\n        /x";
        $value = \preg_replace_callback($pattern, function ($matches) {
            $name = $this->stripQuotes($matches['name']);
            if (':' !== $matches[1]) {
                $name = "'{$name}'";
            }
            $this->boundAttributes = [];
            $attributes = $this->getAttributesFromAttributeString($matches['attributes']);

            return " @slot({$name}, null, [".$this->attributesToString($attributes).']) ';
        }, $value);

        return \preg_replace('/<\\/\\s*x[\\-\\:]slot[^>]*>/', ' @endslot', $value);
    }

    /**
     * Strip any quotes from the given string.
     *
     * @return string
     */
    public function stripQuotes(string $value)
    {
        return Str::startsWith($value, ['"', '\'']) ? \substr($value, 1, -1) : $value;
    }

    /**
     * Compile the opening tags within the given string.
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function compileOpeningTags(string $value)
    {
        $pattern = "/\n            <\n                \\s*\n                x[-\\:]([\\w\\-\\:\\.]*)\n                (?<attributes>\n                    (?:\n                        \\s+\n                        (?:\n                            (?:\n                                \\{\\{\\s*\\\$attributes(?:[^}]+?)?\\s*\\}\\}\n                            )\n                            |\n                            (?:\n                                [\\w\\-:.@]+\n                                (\n                                    =\n                                    (?:\n                                        \\\"[^\\\"]*\\\"\n                                        |\n                                        \\'[^\\']*\\'\n                                        |\n                                        [^\\'\\\"=<>]+\n                                    )\n                                )?\n                            )\n                        )\n                    )*\n                    \\s*\n                )\n                (?<![\\/=\\-])\n            >\n        /x";

        return \preg_replace_callback($pattern, function (array $matches) {
            $this->boundAttributes = [];
            $attributes = $this->getAttributesFromAttributeString($matches['attributes']);

            return $this->componentString($matches[1], $attributes);
        }, $value);
    }

    /**
     * Compile the self-closing tags within the given string.
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function compileSelfClosingTags(string $value)
    {
        $pattern = "/\n            <\n                \\s*\n                x[-\\:]([\\w\\-\\:\\.]*)\n                \\s*\n                (?<attributes>\n                    (?:\n                        \\s+\n                        (?:\n                            (?:\n                                \\{\\{\\s*\\\$attributes(?:[^}]+?)?\\s*\\}\\}\n                            )\n                            |\n                            (?:\n                                [\\w\\-:.@]+\n                                (\n                                    =\n                                    (?:\n                                        \\\"[^\\\"]*\\\"\n                                        |\n                                        \\'[^\\']*\\'\n                                        |\n                                        [^\\'\\\"=<>]+\n                                    )\n                                )?\n                            )\n                        )\n                    )*\n                    \\s*\n                )\n            \\/>\n        /x";

        return \preg_replace_callback($pattern, function (array $matches) {
            $this->boundAttributes = [];
            $attributes = $this->getAttributesFromAttributeString($matches['attributes']);

            return $this->componentString($matches[1], $attributes)."\n@endComponentClass##END-COMPONENT-CLASS##";
        }, $value);
    }

    /**
     * Compile the Blade component string for the given component and attributes.
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function componentString(string $component, array $attributes)
    {
        $class = $this->componentClass($component);
        [$data, $attributes] = $this->partitionDataAndAttributes($class, $attributes);
        $data = $data->mapWithKeys(function ($value, $key) {
            return [Str::camel($key) => $value];
        });
        // If the component doesn't exists as a class we'll assume it's a class-less
        // component and pass the component as a view parameter to the data so it
        // can be accessed within the component and we can render out the view.
        if (!\class_exists($class)) {
            $parameters = ['view' => "'{$class}'", 'data' => '['.$this->attributesToString($data->all(), $escapeBound = \false).']'];
            $class = AnonymousComponent::class;
        } else {
            $parameters = $data->all();
        }

        return "##BEGIN-COMPONENT-CLASS##@component('{$class}', '{$component}', [".$this->attributesToString($parameters, $escapeBound = \false).'])
<?php $component->withAttributes(['.$this->attributesToString($attributes->all(), $escapeAttributes = DynamicComponent::class !== $class).']); ?>';
    }

    /**
     * Compile the closing tags within the given string.
     *
     * @return string
     */
    protected function compileClosingTags(string $value)
    {
        return \preg_replace('/<\\/\\s*x[-\\:][\\w\\-\\:\\.]*\\s*>/', ' @endComponentClass##END-COMPONENT-CLASS##', $value);
    }

    /**
     * Get an array of attributes from the given attribute string.
     *
     * @return array
     */
    protected function getAttributesFromAttributeString(string $attributeString)
    {
        $attributeString = $this->parseAttributeBag($attributeString);
        $attributeString = $this->parseBindAttributes($attributeString);
        $pattern = '/
            (?<attribute>[\\w\\-:.@]+)
            (
                =
                (?<value>
                    (
                        \\"[^\\"]+\\"
                        |
                        \\\'[^\\\']+\\\'
                        |
                        [^\\s>]+
                    )
                )
            )?
        /x';
        if (!\preg_match_all($pattern, $attributeString, $matches, \PREG_SET_ORDER)) {
            return [];
        }

        return collect($matches)->mapWithKeys(function ($match) {
            $attribute = $match['attribute'];
            $value = $match['value'] ?? null;
            if (\is_null($value)) {
                $value = 'true';
                $attribute = Str::start($attribute, 'bind:');
            }
            $value = $this->stripQuotes($value);
            if (Str::startsWith($attribute, 'bind:')) {
                $attribute = Str::after($attribute, 'bind:');
                $this->boundAttributes[$attribute] = \true;
            } else {
                $value = "'".$this->compileAttributeEchos($value)."'";
            }
            if (Str::startsWith($attribute, '::')) {
                $attribute = \substr($attribute, 1);
            }

            return [$attribute => $value];
        })->toArray();
    }

    /**
     * Parse the attribute bag in a given attribute string into its fully-qualified syntax.
     *
     * @return string
     */
    protected function parseAttributeBag(string $attributeString)
    {
        $pattern = "/\n            (?:^|\\s+)                                        # start of the string or whitespace between attributes\n            \\{\\{\\s*(\\\$attributes(?:[^}]+?(?<!\\s))?)\\s*\\}\\} # exact match of attributes variable being echoed\n        /x";

        return \preg_replace($pattern, ' :attributes="$1"', $attributeString);
    }

    /**
     * Parse the "bind" attributes in a given attribute string into their fully-qualified syntax.
     *
     * @return string
     */
    protected function parseBindAttributes(string $attributeString)
    {
        $pattern = "/\n            (?:^|\\s+)     # start of the string or whitespace between attributes\n            :(?!:)        # attribute needs to start with a single colon\n            ([\\w\\-:.@]+)  # match the actual attribute name\n            =             # only match attributes that have a value\n        /xm";

        return \preg_replace($pattern, ' bind:$1=', $attributeString);
    }

    /**
     * Compile any Blade echo statements that are present in the attribute string.
     *
     * These echo statements need to be converted to string concatenation statements.
     *
     * @return string
     */
    protected function compileAttributeEchos(string $attributeString)
    {
        $value = $this->blade->compileEchos($attributeString);
        $value = $this->escapeSingleQuotesOutsideOfPhpBlocks($value);
        $value = \str_replace('<?php echo ', '\'.', $value);

        return \str_replace('; ?>', '.\'', $value);
    }

    /**
     * Escape the single quotes in the given string that are outside of PHP blocks.
     *
     * @return string
     */
    protected function escapeSingleQuotesOutsideOfPhpBlocks(string $value)
    {
        return collect(\token_get_all($value))->map(function ($token) {
            if (!\is_array($token)) {
                return $token;
            }

            return \T_INLINE_HTML === $token[0] ? \str_replace("'", "\\'", $token[1]) : $token[1];
        })->implode('');
    }

    /**
     * Convert an array of attributes to a string.
     *
     * @param bool $escapeBound
     *
     * @return string
     */
    protected function attributesToString(array $attributes, $escapeBound = \true)
    {
        return collect($attributes)->map(function (string $value, string $attribute) use ($escapeBound) {
            return $escapeBound && isset($this->boundAttributes[$attribute]) && 'true' !== $value && !\is_numeric($value) ? "'{$attribute}' => \\Illuminate\\View\\Compilers\\BladeCompiler::sanitizeComponentAttribute({$value})" : "'{$attribute}' => {$value}";
        })->implode(',');
    }
}
