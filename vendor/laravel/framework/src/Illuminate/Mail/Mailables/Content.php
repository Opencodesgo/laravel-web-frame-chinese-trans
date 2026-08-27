<?php
/**
 * Illuminate，邮件，Mailables，内容
 */

namespace Illuminate\Mail\Mailables;

use Illuminate\Support\Traits\Conditionable;

class Content
{
    use Conditionable;

    /**
     * The Blade view that should be rendered for the mailable.
	 * 应该为mailable呈现的Blade视图
     *
     * @var string|null
     */
    public $view;

    /**
     * The Blade view that should be rendered for the mailable.
	 * 应该为mailable呈现的Blade视图
     *
     * Alternative syntax for "view".
     *
     * @var string|null
     */
    public $html;

    /**
     * The Blade view that represents the text version of the message.
	 * 表示消息的文本版本的Blade视图
     *
     * @var string|null
     */
    public $text;

    /**
     * The Blade view that represents the Markdown version of the message.
	 * 表示消息的Markdown版本的Blade视图
     *
     * @var string|null
     */
    public $markdown;

    /**
     * The pre-rendered HTML of the message.
	 * 消息的预呈现HTML
     *
     * @var string|null
     */
    public $htmlString;

    /**
     * The message's view data.
	 * 消息的视图数
     *
     * @var array
     */
    public $with;

    /**
     * Create a new content definition.
	 * 创建一个新的内容定义
     *
     * @param  string|null  $view
     * @param  string|null  $html
     * @param  string|null  $text
     * @param  string|null  $markdown
     * @param  array  $with
     * @param  string|null  $htmlString
     *
     * @named-arguments-supported
     */
    public function __construct(string $view = null, string $html = null, string $text = null, $markdown = null, array $with = [], string $htmlString = null)
    {
        $this->view = $view;
        $this->html = $html;
        $this->text = $text;
        $this->markdown = $markdown;
        $this->with = $with;
        $this->htmlString = $htmlString;
    }

    /**
     * Set the view for the message.
	 * 为消息设置视图
     *
     * @param  string  $view
     * @return $this
     */
    public function view(string $view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Set the view for the message.
	 * 为消息设置视图
     *
     * @param  string  $view
     * @return $this
     */
    public function html(string $view)
    {
        return $this->view($view);
    }

    /**
     * Set the plain text view for the message.
	 * 设置消息的纯文本视图
     *
     * @param  string  $view
     * @return $this
     */
    public function text(string $view)
    {
        $this->text = $view;

        return $this;
    }

    /**
     * Set the Markdown view for the message.
	 * 设置消息的Markdown视图
     *
     * @param  string  $view
     * @return $this
     */
    public function markdown(string $view)
    {
        $this->markdown = $view;

        return $this;
    }

    /**
     * Set the pre-rendered HTML for the message.
	 * 为消息设置预呈现的HTML
     *
     * @param  string  $html
     * @return $this
     */
    public function htmlString(string $html)
    {
        $this->htmlString = $html;

        return $this;
    }

    /**
     * Add a piece of view data to the message.
	 * 向消息添加一段视图数据
     *
     * @param  string  $key
     * @param  mixed|null  $value
     * @return $this
     */
    public function with($key, $value = null)
    {
        if (is_array($key)) {
            $this->with = array_merge($this->with, $key);
        } else {
            $this->with[$key] = $value;
        }

        return $this;
    }
}
