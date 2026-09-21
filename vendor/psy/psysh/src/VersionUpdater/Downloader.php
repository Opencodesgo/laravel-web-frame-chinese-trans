<?php
/**
 * Psy，版本更新，下载器
 */

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2023 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy\VersionUpdater;

use Psy\Exception\ErrorException;

interface Downloader
{
    /**
     * Set the directory where the download will be written to.
	 * 设置将下载写入的目录
     *
     * @param string $tempDir
     */
    public function setTempDir(string $tempDir);

    /**
     * @param string $url
     *
     * @throws ErrorException on failure
     */
    public function download(string $url): bool;

    /**
     * Get the temporary file name the download was written to.
	 * 获取写入下载的临时文件名
     */
    public function getFilename(): string;

    /**
     * Delete the downloaded file if it exists.
	 * 如果下载的文件存在，请删除。
     *
     * @return void
     */
    public function cleanup();
}
