<?php
/**
 * Symfony，Component，HttpFoundation，文件，异常，扩展文件异常
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
 * Thrown when an UPLOAD_ERR_EXTENSION error occurred with UploadedFile.
 * 当UploadedFile发生UPLOAD_ERR_EXTENSION错误时抛出。
 *
 * @author Florent Mata <florentmata@gmail.com>
 */
class ExtensionFileException extends FileException
{
}
