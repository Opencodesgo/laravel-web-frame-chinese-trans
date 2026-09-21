<?php
/**
 * Dotenv，解析器，线 
 */

declare(strict_types=1);

namespace Dotenv\Parser;

use Dotenv\Util\Regex;
use Dotenv\Util\Str;

final class Lines
{
    /**
     * This class is a singleton.
	 * 这个类是单例的
     *
     * @codeCoverageIgnore
     *
     * @return void
     */
    private function __construct()
    {
        //
    }

    /**
     * Process the array of lines of environment variables.
	 * 处理环境变量行数组。
     *
     * This will produce an array of raw entries, one per variable.
	 * 这将生成一个原始条目数组，每个变量一个。
     *
     * @param string[] $lines
     *
     * @return string[]
     */
    public static function process(array $lines)
    {
        $output = [];
        $multiline = false;
        $multilineBuffer = [];

        foreach ($lines as $line) {
            [$multiline, $line, $multilineBuffer] = self::multilineProcess($multiline, $line, $multilineBuffer);

            if (!$multiline && !self::isCommentOrWhitespace($line)) {
                $output[] = $line;
            }
        }

        return $output;
    }

    /**
     * Used to make all multiline variable process.
	 * 用于制作所有多行可变工艺
     *
     * @param bool     $multiline
     * @param string   $line
     * @param string[] $buffer
     *
     * @return array{bool,string, string[]}
     */
    private static function multilineProcess(bool $multiline, string $line, array $buffer)
    {
        $startsOnCurrentLine = $multiline ? false : self::looksLikeMultilineStart($line);

        // check if $line can be multiline variable
		// 检查$line是否可以是多行变量
        if ($startsOnCurrentLine) {
            $multiline = true;
        }

        if ($multiline) {
            \array_push($buffer, $line);

            if (self::looksLikeMultilineStop($line, $startsOnCurrentLine)) {
                $multiline = false;
                $line = \implode("\n", $buffer);
                $buffer = [];
            }
        }

        return [$multiline, $line, $buffer];
    }

    /**
     * Determine if the given line can be the start of a multiline variable.
	 * 确定给定的行是否可以作为多行变量的开始
     *
     * @param string $line
     *
     * @return bool
     */
    private static function looksLikeMultilineStart(string $line)
    {
        return Str::pos($line, '="')->map(static function () use ($line) {
            return self::looksLikeMultilineStop($line, true) === false;
        })->getOrElse(false);
    }

    /**
     * Determine if the given line can be the start of a multiline variable.
	 * 确定给定的行是否可以作为多行变量的开始
     *
     * @param string $line
     * @param bool   $started
     *
     * @return bool
     */
    private static function looksLikeMultilineStop(string $line, bool $started)
    {
        if ($line === '"') {
            return true;
        }

        return Regex::occurrences('/(?=([^\\\\]"))/', \str_replace('\\\\', '', $line))->map(static function (int $count) use ($started) {
            return $started ? $count > 1 : $count >= 1;
        })->success()->getOrElse(false);
    }

    /**
     * Determine if the line in the file is a comment or whitespace.
	 * 确定文件中的行是注释还是空白
     *
     * @param string $line
     *
     * @return bool
     */
    private static function isCommentOrWhitespace(string $line)
    {
        $line = \trim($line);

        return $line === '' || (isset($line[0]) && $line[0] === '#');
    }
}
