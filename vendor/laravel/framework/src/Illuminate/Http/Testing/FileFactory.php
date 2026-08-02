<?php
/**
 * Illuminate，Http，测试，文件工厂
 */

namespace Illuminate\Http\Testing;

class FileFactory
{
    /**
     * Create a new fake file.
	 * 创建一个新的假文件
     *
     * @param  string  $name
     * @param  string|int  $kilobytes
     * @param  string|null  $mimeType
     * @return \Illuminate\Http\Testing\File
     */
    public function create($name, $kilobytes = 0, $mimeType = null)
    {
        if (is_string($kilobytes)) {
            return $this->createWithContent($name, $kilobytes);
        }

        return tap(new File($name, tmpfile()), function ($file) use ($kilobytes, $mimeType) {
            $file->sizeToReport = $kilobytes * 1024;
            $file->mimeTypeToReport = $mimeType;
        });
    }

    /**
     * Create a new fake file with content.
	 * 创建一个包含内容的新假文件
     *
     * @param  string  $name
     * @param  string  $content
     * @return \Illuminate\Http\Testing\File
     */
    public function createWithContent($name, $content)
    {
        $tmpfile = tmpfile();

        fwrite($tmpfile, $content);

        return tap(new File($name, $tmpfile), function ($file) use ($tmpfile) {
            $file->sizeToReport = fstat($tmpfile)['size'];
        });
    }

    /**
     * Create a new fake image.
	 * 创建新的假图片
     *
     * @param  string  $name
     * @param  int  $width
     * @param  int  $height
     * @return \Illuminate\Http\Testing\File
     */
    public function image($name, $width = 10, $height = 10)
    {
        return new File($name, $this->generateImage(
            $width, $height, pathinfo($name, PATHINFO_EXTENSION)
        ));
    }

    /**
     * Generate a dummy image of the given width and height.
	 * 生成给定宽度和高度的虚拟图像
     *
     * @param  int  $width
     * @param  int  $height
     * @param  string  $extension
     * @return resource
     */
    protected function generateImage($width, $height, $extension)
    {
        return tap(tmpfile(), function ($temp) use ($width, $height, $extension) {
            ob_start();

            $extension = in_array($extension, ['jpeg', 'png', 'gif', 'webp', 'wbmp', 'bmp'])
                ? strtolower($extension)
                : 'jpeg';

            $image = imagecreatetruecolor($width, $height);

            call_user_func("image{$extension}", $image);

            fwrite($temp, ob_get_clean());
        });
    }
}
