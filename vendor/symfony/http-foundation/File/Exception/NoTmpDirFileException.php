<?php
/**
 * Symfony，Component，HttpFoundation，文件，异常，无临时目录文件异常
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
 * Thrown when an UPLOAD_ERR_NO_TMP_DIR error occurred with UploadedFile.
 * 当UploadedFile发生UPLOAD_ERR_NO_TMP_DIR错误时抛出
 *
 * @author Florent Mata <florentmata@gmail.com>
 */
class NoTmpDirFileException extends FileException
{
}
