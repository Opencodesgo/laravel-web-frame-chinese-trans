<?php
/**
 * Psy，版本更新，安装程序
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

class Installer
{
    /**
     * @var string
     */
    protected $installLocation;

    /**
     * @var string
     */
    protected $tempDirectory;

    public function __construct(?string $tempDirectory = null)
    {
        $this->tempDirectory = $tempDirectory ?: \sys_get_temp_dir();
        $this->installLocation = \Phar::running(false);
    }

    /**
     * Public to allow the Downloader to use the temporary directory if it's been set.
	 * 如果设置了临时目录，则允许下载程序使用。
     */
    public function getTempDirectory(): string
    {
        return $this->tempDirectory;
    }

    /**
     * Verify the currently installed PsySH phar is writable so it can be replaced.
	 * 验证当前安装的PsySH文件是可写的，以便可以替换它。
     */
    public function isInstallLocationWritable(): bool
    {
        return \is_writable($this->installLocation);
    }

    /**
     * Verify the temporary directory is writable so downloads and backups can be saved there.
	 * 验证临时目录是可写的，以便下载和备份可以保存在那里。
     */
    public function isTempDirectoryWritable(): bool
    {
        return \is_writable($this->tempDirectory);
    }

    /**
     * Verifies the downloaded archive can be extracted with \PharData.
	 * 验证下载的存档文件可以用\PharData提取
     */
    public function isValidSource(string $sourceArchive): bool
    {
        if (!\class_exists('\PharData')) {
            return false;
        }
        $pharArchive = new \PharData($sourceArchive);

        return $pharArchive->valid();
    }

    /**
     * Extract the "psysh" phar from the archive and move it, replacing the currently installed phar.
     */
    public function install(string $sourceArchive): bool
    {
        $pharArchive = new \PharData($sourceArchive);
        $outputDirectory = \tempnam($this->tempDirectory, 'psysh-');

        // remove the temp file, and replace it with a sub-directory
        if (!\unlink($outputDirectory) || !\mkdir($outputDirectory, 0700)) {
            return false;
        }

        $pharArchive->extractTo($outputDirectory, ['psysh'], true);

        $renamed = \rename($outputDirectory.'/psysh', $this->installLocation);

        // Remove the sub-directory created to extract the psysh binary/phar
        \rmdir($outputDirectory);

        return $renamed;
    }

    /**
     * Create a backup of the currently installed PsySH phar in the temporary directory with a version number postfix.
	 * 在带有版本号后缀的临时目录中创建当前安装的PsySH phar的备份。
     */
    public function createBackup(string $version): bool
    {
        $backupFilename = $this->getBackupFilename($version);

        if (\file_exists($backupFilename) && !\is_writable($backupFilename)) {
            return false;
        }

        return \rename($this->installLocation, $backupFilename);
    }

    /**
     * Restore the backup file to the original PsySH install location.
	 * 将备份文件恢复到原来的PsySH安装位置
     *
     * @throws ErrorException If the backup file could not be found
     */
    public function restoreFromBackup(string $version): bool
    {
        $backupFilename = $this->getBackupFilename($version);

        if (!\file_exists($backupFilename)) {
            throw new ErrorException("Cannot restore from backup. File not found! [{$backupFilename}]");
        }

        return \rename($backupFilename, $this->installLocation);
    }

    /**
     * Get the full path for the backup target file location.
	 * 获取备份目标文件位置的完整路径
     */
    public function getBackupFilename(string $version): string
    {
        $installFilename = \basename($this->installLocation);

        return \sprintf('%s/%s.%s', $this->tempDirectory, $installFilename, $version);
    }
}
