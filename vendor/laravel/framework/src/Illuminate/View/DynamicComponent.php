<?php
/**
 * Illuminate，视图，动态组件
 */

namespace Illuminate\View;

use Illuminate\Container\Container;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\ComponentTagCompiler;

class DynamicComponent extends Component
{
    /**
     * The name of the component.
	 * 组件的名称
     *
     * @var string
     */
    public $component;

    /**
     * The component tag compiler instance.
	 * 组件标签编译器实例
     *
     * @var \Illuminate\View\Compilers\BladeTagCompiler
     */
    protected static $compiler;

    /**
     * The cached component classes.
	 * 缓存的组件类
     *
     * @var array
     */
    protected static $componentClasses = [];

    /**
     * Create a new component instance.
	 * 创建一个新的组件实例
     *
     * @param  string  $component
     * @return void
     */
    public function __construct(string $component)
    {
        $this->component = $component;
    }

    /**
     * Get the view / contents that represent the component.
	 * 获取表示组件的视图/内容
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        $template = <<<'EOF'
<?php extract(collect($attributes->getAttributes())->mapWithKeys(function ($value, $key) { return [Illuminate\Support\Str::camel(str_replace([':', '.'], ' ', $key)) => $value]; })->all(), EXTR_SKIP); ?>
{{ props }}
<x-{{ component }} {{ bindings }} {{ attributes }}>
{{ slots }}
{{ defaultSlot }}
</x-{{ component }}>
EOF;

        return function ($data) use ($template) {
            $bindings = $this->bindings($class = $this->classForComponent());

            return str_replace(
                [
                    '{{ component }}',
                    '{{ props }}',
                    '{{ bindings }}',
                    '{{ attributes }}',
                    '{{ slots }}',
                    '{{ defaultSlot }}',
                ],
                [
                    $this->component,
                    $this->compileProps($bindings),
                    $this->compileBindings($bindings),
                    class_exists($class) ? '{{ $attributes }}' : '',
                    $this->compileSlots($data['__laravel_slots']),
                    '{{ $slot ?? "" }}',
                ],
                $template
            );
        };
    }

    /**
     * Compile the @props directive for the component.
	 * 为组件编译@props指令
     *
     * @param  array  $bindings
     * @return string
     */
    protected function compileProps(array $bindings)
    {
        if (empty($bindings)) {
            return '';
        }

        return '@props('.'[\''.implode('\',\'', collect($bindings)->map(function ($dataKey) {
            return Str::camel($dataKey);
        })->all()).'\']'.')';
    }

    /**
     * Compile the bindings for the component.
	 * 为组件编译绑定
     *
     * @param  array  $bindings
     * @return string
     */
    protected function compileBindings(array $bindings)
    {
        return collect($bindings)->map(function ($key) {
            return ':'.$key.'="$'.Str::camel(str_replace([':', '.'], ' ', $key)).'"';
        })->implode(' ');
    }

    /**
     * Compile the slots for the component.
	 * 编译组件的插槽
     *
     * @param  array  $slots
     * @return string
     */
    protected function compileSlots(array $slots)
    {
        return collect($slots)->map(function ($slot, $name) {
            return $name === '__default' ? null : '<x-slot name="'.$name.'" '.((string) $slot->attributes).'>{{ $'.$name.' }}</x-slot>';
        })->filter()->implode(PHP_EOL);
    }

    /**
     * Get the class for the current component.
	 * 获取当前组件的类
     *
     * @return string
     */
    protected function classForComponent()
    {
        if (isset(static::$componentClasses[$this->component])) {
            return static::$componentClasses[$this->component];
        }

        return static::$componentClasses[$this->component] =
                    $this->compiler()->componentClass($this->component);
    }

    /**
     * Get the names of the variables that should be bound to the component.
	 * 获取应该绑定到组件的变量的名称
     *
     * @param  string  $class
     * @return array
     */
    protected function bindings(string $class)
    {
        [$data, $attributes] = $this->compiler()->partitionDataAndAttributes($class, $this->attributes->getAttributes());

        return array_keys($data->all());
    }

    /**
     * Get an instance of the Blade tag compiler.
	 * 获取Blade标记编译器的实例
     *
     * @return \Illuminate\View\Compilers\ComponentTagCompiler
     */
    protected function compiler()
    {
        if (! static::$compiler) {
            static::$compiler = new ComponentTagCompiler(
                Container::getInstance()->make('blade.compiler')->getClassComponentAliases(),
                Container::getInstance()->make('blade.compiler')->getClassComponentNamespaces(),
                Container::getInstance()->make('blade.compiler')
            );
        }

        return static::$compiler;
    }
}
