<?php
/**
 * Symfony，Component，CssSelector，解析器，令牌流
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\CssSelector\Parser;

use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\CssSelector\Exception\SyntaxErrorException;

/**
 * CSS selector token stream.
 * CSS选择器标记流。
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class TokenStream
{
    /**
     * @var Token[]
     */
    private array $tokens = [];

    /**
     * @var Token[]
     */
    private array $used = [];

    private int $cursor = 0;
    private ?Token $peeked;
    private bool $peeking = false;

    /**
     * Pushes a token.
	 * 推令牌
     *
     * @return $this
     */
    public function push(Token $token): static
    {
        $this->tokens[] = $token;

        return $this;
    }

    /**
     * Freezes stream.
	 * 冻结流
     *
     * @return $this
     */
    public function freeze(): static
    {
        return $this;
    }

    /**
     * Returns next token.
	 * 返回下一个令牌
     *
     * @throws InternalErrorException If there is no more token
     */
    public function getNext(): Token
    {
        if ($this->peeking) {
            $this->peeking = false;
            $this->used[] = $this->peeked;

            return $this->peeked;
        }

        if (!isset($this->tokens[$this->cursor])) {
            throw new InternalErrorException('Unexpected token stream end.');
        }

        return $this->tokens[$this->cursor++];
    }

    /**
     * Returns peeked token.
	 * 返回窥视令牌
     */
    public function getPeek(): Token
    {
        if (!$this->peeking) {
            $this->peeked = $this->getNext();
            $this->peeking = true;
        }

        return $this->peeked;
    }

    /**
     * Returns used tokens.
	 * 返回使用过的令牌
     *
     * @return Token[]
     */
    public function getUsed(): array
    {
        return $this->used;
    }

    /**
     * Returns next identifier token.
	 * 返回下一个标识令牌
     *
     * @throws SyntaxErrorException If next token is not an identifier
     */
    public function getNextIdentifier(): string
    {
        $next = $this->getNext();

        if (!$next->isIdentifier()) {
            throw SyntaxErrorException::unexpectedToken('identifier', $next);
        }

        return $next->getValue();
    }

    /**
     * Returns next identifier or null if star delimiter token is found.
	 * 返回下一个标识符，如果找到星号分隔符标记则返回空。
     *
     * @throws SyntaxErrorException If next token is not an identifier or a star delimiter
     */
    public function getNextIdentifierOrStar(): ?string
    {
        $next = $this->getNext();

        if ($next->isIdentifier()) {
            return $next->getValue();
        }

        if ($next->isDelimiter(['*'])) {
            return null;
        }

        throw SyntaxErrorException::unexpectedToken('identifier or "*"', $next);
    }

    /**
     * Skips next whitespace if any.
	 * 跳过下一个空格（如果有的话）
     */
    public function skipWhitespace(): void
    {
        $peek = $this->getPeek();

        if ($peek->isWhitespace()) {
            $this->getNext();
        }
    }
}
