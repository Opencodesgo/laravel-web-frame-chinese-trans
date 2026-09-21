<?php
/**
 * Illuminate，邮件，附件
 */

namespace Illuminate\Mail;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Support\Traits\Macroable;

class Attachment
{
    use Macroable;

    /**
     * The attached file's filename.
	 * 附加文件的文件名
     *
     * @var string|null
     */
    public $as;

    /**
     * The attached file's mime type.
	 * 附加文件的mime类型
     *
     * @var string|null
     */
    public $mime;

    /**
     * A callback that attaches the attachment to the mail message.
	 * 将附件附加到邮件消息的回调
     *
     * @var \Closure
     */
    protected $resolver;

    /**
     * Create a mail attachment.
	 * 创建邮件附件
     *
     * @param  \Closure  $resolver
     * @return void
     */
    private function __construct(Closure $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Create a mail attachment from a path.
	 * 从路径创建邮件附件
     *
     * @param  string  $path
     * @return static
     */
    public static function fromPath($path)
    {
        return new static(fn ($attachment, $pathStrategy) => $pathStrategy($path, $attachment));
    }

    /**
     * Create a mail attachment from in-memory data.
	 * 从内存数据创建邮件附件
     *
     * @param  \Closure  $data
     * @param  string  $name
     * @return static
     */
    public static function fromData(Closure $data, $name)
    {
        return (new static(
            fn ($attachment, $pathStrategy, $dataStrategy) => $dataStrategy($data, $attachment)
        ))->as($name);
    }

    /**
     * Create a mail attachment from a file in the default storage disk.
	 * 从默认存储磁盘中的文件创建邮件附件
     *
     * @param  string  $path
     * @return static
     */
    public static function fromStorage($path)
    {
        return static::fromStorageDisk(null, $path);
    }

    /**
     * Create a mail attachment from a file in the specified storage disk.
	 * 从指定存储磁盘中的文件创建邮件附件
     *
     * @param  string|null  $disk
     * @param  string  $path
     * @return static
     */
    public static function fromStorageDisk($disk, $path)
    {
        return new static(function ($attachment, $pathStrategy, $dataStrategy) use ($disk, $path) {
            $storage = Container::getInstance()->make(
                FilesystemFactory::class
            )->disk($disk);

            $attachment
                ->as($attachment->as ?? basename($path))
                ->withMime($attachment->mime ?? $storage->mimeType($path));

            return $dataStrategy(fn () => $storage->get($path), $attachment);
        });
    }

    /**
     * Set the attached file's filename.
	 * 设置附加文件的文件名
     *
     * @param  string  $name
     * @return $this
     */
    public function as($name)
    {
        $this->as = $name;

        return $this;
    }

    /**
     * Set the attached file's mime type.
	 * 设置附加文件的mime类型
     *
     * @param  string  $mime
     * @return $this
     */
    public function withMime($mime)
    {
        $this->mime = $mime;

        return $this;
    }

    /**
     * Attach the attachment with the given strategies.
	 * 将附件与给定的策略一起附上
     *
     * @param  \Closure  $pathStrategy
     * @param  \Closure  $dataStrategy
     * @return mixed
     */
    public function attachWith(Closure $pathStrategy, Closure $dataStrategy)
    {
        return ($this->resolver)($this, $pathStrategy, $dataStrategy);
    }

    /**
     * Attach the attachment to a built-in mail type.
	 * 将附件附加到内置邮件类型
     *
     * @param  \Illuminate\Mail\Mailable|\Illuminate\Mail\Message|\Illuminate\Notifications\Messages\MailMessage  $mail
     * @return mixed
     */
    public function attachTo($mail)
    {
        return $this->attachWith(
            fn ($path) => $mail->attach($path, ['as' => $this->as, 'mime' => $this->mime]),
            fn ($data) => $mail->attachData($data(), $this->as, ['mime' => $this->mime])
        );
    }

    /**
     * Determine if the given attachment is equivalent to this attachment.
	 * 确定给定的附件是否等同于此附件
     *
     * @param  \Illuminate\Mail\Attachment  $attachment
     * @return bool
     */
    public function isEquivalent(Attachment $attachment)
    {
        return $this->attachWith(
            fn ($path) => [$path, ['as' => $this->as, 'mime' => $this->mime]],
            fn ($data) => [$data(), ['as' => $this->as, 'mime' => $this->mime]],
        ) === $attachment->attachWith(
            fn ($path) => [$path, ['as' => $attachment->as, 'mime' => $attachment->mime]],
            fn ($data) => [$data(), ['as' => $attachment->as, 'mime' => $attachment->mime]],
        );
    }
}
