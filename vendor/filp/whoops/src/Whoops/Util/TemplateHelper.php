<?php
/**
 * Whoops，工具，模板辅助
 */

/**
 * Whoops - php errors for cool kids
 * @author Filipe Dobreira <http://github.com/filp>
 */

namespace Whoops\Util;

use Symfony\Component\VarDumper\Caster\Caster;
use Symfony\Component\VarDumper\Cloner\AbstractCloner;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Whoops\Exception\Frame;

/**
 * Exposes useful tools for working with/in templates
 * 公开使用/in模板的有用工具
 */
class TemplateHelper
{
    /**
     * An array of variables to be passed to all templates
	 * 传递给所有模板的变量数组
     * @var array
     */
    private $variables = [];

    /**
     * @var HtmlDumper
     */
    private $htmlDumper;

    /**
     * @var HtmlDumperOutput
     */
    private $htmlDumperOutput;

    /**
     * @var AbstractCloner
     */
    private $cloner;

    /**
     * @var string
     */
    private $applicationRootPath;

    public function __construct()
    {
        // root path for ordinary composer projects
        $this->applicationRootPath = dirname(dirname(dirname(dirname(dirname(dirname(__DIR__))))));
    }

    /**
     * Escapes a string for output in an HTML document
	 * 转义要在HTML文档中输出的字符串
     *
     * @param  string $raw
     * @return string
     */
    public function escape($raw)
    {
        $flags = ENT_QUOTES;

        // HHVM has all constants defined, but only ENT_IGNORE
        // works at the moment
        if (defined("ENT_SUBSTITUTE") && !defined("HHVM_VERSION")) {
            $flags |= ENT_SUBSTITUTE;
        } else {
            // This is for 5.3.
            // The documentation warns of a potential security issue,
            // but it seems it does not apply in our case, because
            // we do not blacklist anything anywhere.
            $flags |= ENT_IGNORE;
        }

        $raw = str_replace(chr(9), '    ', $raw);

        return htmlspecialchars($raw, $flags, "UTF-8");
    }

    /**
     * Escapes a string for output in an HTML document, but preserves
     * URIs within it, and converts them to clickable anchor elements.
     *
     * @param  string $raw
     * @return string
     */
    public function escapeButPreserveUris($raw)
    {
        $escaped = $this->escape($raw);
        return preg_replace(
            "@([A-z]+?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@",
            "<a href=\"$1\" target=\"_blank\" rel=\"noreferrer noopener\">$1</a>",
            $escaped
        );
    }

    /**
     * Makes sure that the given string breaks on the delimiter.
	 * 确保给定的字符串在分隔符上断开
     *
     * @param  string $delimiter
     * @param  string $s
     * @return string
     */
    public function breakOnDelimiter($delimiter, $s)
    {
        $parts = explode($delimiter, $s);
        foreach ($parts as &$part) {
            $part = '<span class="delimiter">' . $part . '</span>';
        }

        return implode($delimiter, $parts);
    }

    /**
     * Replace the part of the path that all files have in common.
	 * 替换路径中所有文件共有的部分
     *
     * @param  string $path
     * @return string
     */
    public function shorten($path)
    {
        if ($this->applicationRootPath != "/") {
            $path = str_replace($this->applicationRootPath, '&hellip;', $path);
        }

        return $path;
    }

    private function getDumper()
    {
        if (!$this->htmlDumper && class_exists('Symfony\Component\VarDumper\Cloner\VarCloner')) {
            $this->htmlDumperOutput = new HtmlDumperOutput();
            // re-use the same var-dumper instance, so it won't re-render the global styles/scripts on each dump.
            $this->htmlDumper = new HtmlDumper($this->htmlDumperOutput);

            $styles = [
                'default' => 'color:#FFFFFF; line-height:normal; font:12px "Inconsolata", "Fira Mono", "Source Code Pro", Monaco, Consolas, "Lucida Console", monospace !important; word-wrap: break-word; white-space: pre-wrap; position:relative; z-index:99999; word-break: normal',
                'num' => 'color:#BCD42A',
                'const' => 'color: #4bb1b1;',
                'str' => 'color:#BCD42A',
                'note' => 'color:#ef7c61',
                'ref' => 'color:#A0A0A0',
                'public' => 'color:#FFFFFF',
                'protected' => 'color:#FFFFFF',
                'private' => 'color:#FFFFFF',
                'meta' => 'color:#FFFFFF',
                'key' => 'color:#BCD42A',
                'index' => 'color:#ef7c61',
            ];
            $this->htmlDumper->setStyles($styles);
        }

        return $this->htmlDumper;
    }

    /**
     * Format the given value into a human readable string.
	 * 将给定值格式化为人类可读的字符串
     *
     * @param  mixed $value
     * @return string
     */
    public function dump($value)
    {
        $dumper = $this->getDumper();

        if ($dumper) {
            // re-use the same DumpOutput instance, so it won't re-render the global styles/scripts on each dump.
            // exclude verbose information (e.g. exception stack traces)
            if (class_exists('Symfony\Component\VarDumper\Caster\Caster')) {
                $cloneVar = $this->getCloner()->cloneVar($value, Caster::EXCLUDE_VERBOSE);
                // Symfony VarDumper 2.6 Caster class dont exist.
            } else {
                $cloneVar = $this->getCloner()->cloneVar($value);
            }

            $dumper->dump(
                $cloneVar,
                $this->htmlDumperOutput
            );

            $output = $this->htmlDumperOutput->getOutput();
            $this->htmlDumperOutput->clear();

            return $output;
        }

        return htmlspecialchars(print_r($value, true));
    }

    /**
     * Format the args of the given Frame as a human readable html string
	 * 将给定框架的参数格式化为人类可读的html字符串
     *
     * @param  Frame $frame
     * @return string the rendered html
     */
    public function dumpArgs(Frame $frame)
    {
        // we support frame args only when the optional dumper is available
        if (!$this->getDumper()) {
            return '';
        }

        $html = '';
        $numFrames = count($frame->getArgs());

        if ($numFrames > 0) {
            $html = '<ol class="linenums">';
            foreach ($frame->getArgs() as $j => $frameArg) {
                $html .= '<li>'. $this->dump($frameArg) .'</li>';
            }
            $html .= '</ol>';
        }

        return $html;
    }

    /**
     * Convert a string to a slug version of itself
	 * 将字符串转换为其自身的鼻涕虫版本
     *
     * @param  string $original
     * @return string
     */
    public function slug($original)
    {
        $slug = str_replace(" ", "-", $original);
        $slug = preg_replace('/[^\w\d\-\_]/i', '', $slug);
        return strtolower($slug);
    }

    /**
     * Given a template path, render it within its own scope. This
     * method also accepts an array of additional variables to be
     * passed to the template.
     *
     * @param string $template
     */
    public function render($template, ?array $additionalVariables = null)
    {
        $variables = $this->getVariables();

        // Pass the helper to the template:
        $variables["tpl"] = $this;

        if ($additionalVariables !== null) {
            $variables = array_replace($variables, $additionalVariables);
        }

        call_user_func(function () {
            extract(func_get_arg(1));
            require func_get_arg(0);
        }, $template, $variables);
    }

    /**
     * Sets the variables to be passed to all templates rendered
     * by this template helper.
     */
    public function setVariables(array $variables)
    {
        $this->variables = $variables;
    }

    /**
     * Sets a single template variable, by its name:
	 * 按其名称设置单个模板变量：
     *
     * @param string $variableName
     * @param mixed  $variableValue
     */
    public function setVariable($variableName, $variableValue)
    {
        $this->variables[$variableName] = $variableValue;
    }

    /**
     * Gets a single template variable, by its name, or
     * $defaultValue if the variable does not exist
     *
     * @param  string $variableName
     * @param  mixed  $defaultValue
     * @return mixed
     */
    public function getVariable($variableName, $defaultValue = null)
    {
        return isset($this->variables[$variableName]) ?
            $this->variables[$variableName] : $defaultValue;
    }

    /**
     * Unsets a single template variable, by its name
	 * 按名称取消设置单个模板变量
     *
     * @param string $variableName
     */
    public function delVariable($variableName)
    {
        unset($this->variables[$variableName]);
    }

    /**
     * Returns all variables for this helper
	 * 返回此帮助器的所有变量
     *
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * Set the cloner used for dumping variables.
	 * 设置用于转储变量的克隆器
     *
     * @param AbstractCloner $cloner
     */
    public function setCloner($cloner)
    {
        $this->cloner = $cloner;
    }

    /**
     * Get the cloner used for dumping variables.
	 * 获取用于转储变量的克隆器
     *
     * @return AbstractCloner
     */
    public function getCloner()
    {
        if (!$this->cloner) {
            $this->cloner = new VarCloner();
        }
        return $this->cloner;
    }

    /**
     * Set the application root path.
	 * 设置应用程序根路径
     *
     * @param string $applicationRootPath
     */
    public function setApplicationRootPath($applicationRootPath)
    {
        $this->applicationRootPath = $applicationRootPath;
    }

    /**
     * Return the application root path.
	 * 返回应用程序根路径
     *
     * @return string
     */
    public function getApplicationRootPath()
    {
        return $this->applicationRootPath;
    }
}
