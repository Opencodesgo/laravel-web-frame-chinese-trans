<?php
/**
 * Symfony，Component，HttpFoundation，文件，异常，上传异常
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation\File\Exception;

/**
 * Thrown when an error occurred during file upload.
 * 当文件上传过程中发生错误时抛出
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class UploadException extends FileException
{
}
