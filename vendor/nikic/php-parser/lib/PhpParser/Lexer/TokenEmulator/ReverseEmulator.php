<?php declare(strict_types=1);

/**
 * PhpParser，Lexer，内部，令牌仿真器，反向模拟器
 */

namespace PhpParser\Lexer\TokenEmulator;

use PhpParser\PhpVersion;

/**
 * Reverses emulation direction of the inner emulator.
 * 反转内部仿真器的仿真方向。
 */
final class ReverseEmulator extends TokenEmulator {
    /** @var TokenEmulator Inner emulator */
    private TokenEmulator $emulator;

    public function __construct(TokenEmulator $emulator) {
        $this->emulator = $emulator;
    }

    public function getPhpVersion(): PhpVersion {
        return $this->emulator->getPhpVersion();
    }

    public function isEmulationNeeded(string $code): bool {
        return $this->emulator->isEmulationNeeded($code);
    }

    public function emulate(string $code, array $tokens): array {
        return $this->emulator->reverseEmulate($code, $tokens);
    }

    public function reverseEmulate(string $code, array $tokens): array {
        return $this->emulator->emulate($code, $tokens);
    }

    public function preprocessCode(string $code, array &$patches): string {
        return $code;
    }
}
