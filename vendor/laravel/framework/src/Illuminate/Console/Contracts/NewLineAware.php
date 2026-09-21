<?php
/**
 * Illuminate，控制台，契约，意识到行
 */

namespace Illuminate\Console\Contracts;

interface NewLineAware
{
    /**
     * Whether a newline has already been written.
	 * 是否已经写了换行符
     *
     * @return bool
     */
    public function newLineWritten();
}
