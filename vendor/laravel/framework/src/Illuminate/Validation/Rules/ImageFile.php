<?php
/**
 * Illuminate，验证，规则，镜像文件
 */

namespace Illuminate\Validation\Rules;

class ImageFile extends File
{
    /**
     * Create a new image file rule instance.
	 * 创建一个新的映像文件规则实例
     *
     * @return void
     */
    public function __construct()
    {
        $this->rules('image');
    }

    /**
     * The dimension constraints for the uploaded file.
	 * 上传文件的维度约束
     *
     * @param  \Illuminate\Validation\Rules\Dimensions  $dimensions
     */
    public function dimensions($dimensions)
    {
        $this->rules($dimensions);

        return $this;
    }
}
