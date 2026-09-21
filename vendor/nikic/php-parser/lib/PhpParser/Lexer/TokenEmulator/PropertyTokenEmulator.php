<?php declare(strict_types=1);

/**
 * PhpParser，Lexer，内部，令牌仿真器，属性令牌仿真器
 */

namespace PhpParser\Lexer\TokenEmulator;

use PhpParser\PhpVersion;

final class PropertyTokenEmulator extends KeywordEmulator {
    public function getPhpVersion(): PhpVersion {
        return PhpVersion::fromComponents(8, 4);
    }

    public function getKeywordString(): string {
        return '__property__';
    }

    public function getKeywordToken(): int {
        return \T_PROPERTY_C;
    }
}
