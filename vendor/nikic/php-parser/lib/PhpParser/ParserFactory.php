<?php declare(strict_types=1);

/**
 * PhpParser，解析器工厂
 */

namespace PhpParser;

use PhpParser\Parser\Php7;
use PhpParser\Parser\Php8;

class ParserFactory {
    /**
     * Create a parser targeting the given version on a best-effort basis. The parser will generally
     * accept code for the newest supported version, but will try to accommodate code that becomes
     * invalid in newer versions or changes in interpretation.
	 * 尽最大努力创建一个针对给定版本的解析器。
     */
    public function createForVersion(PhpVersion $version): Parser {
        if ($version->isHostVersion()) {
            $lexer = new Lexer();
        } else {
            $lexer = new Lexer\Emulative($version);
        }
        if ($version->id >= 80000) {
            return new Php8($lexer, $version);
        }
        return new Php7($lexer, $version);
    }

    /**
     * Create a parser targeting the newest version supported by this library. Code for older
     * versions will be accepted if there have been no relevant backwards-compatibility breaks in
     * PHP.
	 * 创建一个针对此库支持的最新版本的解析器。
     */
    public function createForNewestSupportedVersion(): Parser {
        return $this->createForVersion(PhpVersion::getNewestSupported());
    }

    /**
     * Create a parser targeting the host PHP version, that is the PHP version we're currently
     * running on. This parser will not use any token emulation.
	 * 创建一个针对主机PHP版本的解析器，也就是我们当前使用的PHP版本。
     */
    public function createForHostVersion(): Parser {
        return $this->createForVersion(PhpVersion::getHostVersion());
    }
}
