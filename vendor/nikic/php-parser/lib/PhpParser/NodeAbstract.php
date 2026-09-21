<?php declare(strict_types=1);

/**
 * PhpParser，结点抽象
 */

namespace PhpParser;

abstract class NodeAbstract implements Node, \JsonSerializable {
    /** @var array<string, mixed> Attributes */
    protected array $attributes;

    /**
     * Creates a Node.
	 * 创建节点
     *
     * @param array<string, mixed> $attributes Array of attributes
     */
    public function __construct(array $attributes = []) {
        $this->attributes = $attributes;
    }

    /**
     * Gets line the node started in (alias of getStartLine).
	 * 获取开始所在节点的行（getStartLine的别名）
     *
     * @return int Start line (or -1 if not available)
     * @phpstan-return -1|positive-int
     */
    public function getLine(): int {
        return $this->attributes['startLine'] ?? -1;
    }

    /**
     * Gets line the node started in.
	 * 获取节点开始所在的行。
     *
     * Requires the 'startLine' attribute to be enabled in the lexer (enabled by default).
     *
     * @return int Start line (or -1 if not available)
     * @phpstan-return -1|positive-int
     */
    public function getStartLine(): int {
        return $this->attributes['startLine'] ?? -1;
    }

    /**
     * Gets the line the node ended in.
	 * 获取节点结束的行。
     *
     * Requires the 'endLine' attribute to be enabled in the lexer (enabled by default).
     *
     * @return int End line (or -1 if not available)
     * @phpstan-return -1|positive-int
     */
    public function getEndLine(): int {
        return $this->attributes['endLine'] ?? -1;
    }

    /**
     * Gets the token offset of the first token that is part of this node.
	 * 获取作为此节点一部分的第一个令牌的令牌偏移量。
     *
     * The offset is an index into the array returned by Lexer::getTokens().
     *
     * Requires the 'startTokenPos' attribute to be enabled in the lexer (DISABLED by default).
     *
     * @return int Token start position (or -1 if not available)
     */
    public function getStartTokenPos(): int {
        return $this->attributes['startTokenPos'] ?? -1;
    }

    /**
     * Gets the token offset of the last token that is part of this node.
	 * 获取作为此节点一部分的最后一个令牌的令牌偏移量。
     *
     * The offset is an index into the array returned by Lexer::getTokens().
     *
     * Requires the 'endTokenPos' attribute to be enabled in the lexer (DISABLED by default).
     *
     * @return int Token end position (or -1 if not available)
     */
    public function getEndTokenPos(): int {
        return $this->attributes['endTokenPos'] ?? -1;
    }

    /**
     * Gets the file offset of the first character that is part of this node.
	 * 获取作为此节点一部分的第一个字符的文件偏移量。
     *
     * Requires the 'startFilePos' attribute to be enabled in the lexer (DISABLED by default).
     *
     * @return int File start position (or -1 if not available)
     */
    public function getStartFilePos(): int {
        return $this->attributes['startFilePos'] ?? -1;
    }

    /**
     * Gets the file offset of the last character that is part of this node.
	 * 获取作为该节点一部分的最后一个字符的文件偏移量。
     *
     * Requires the 'endFilePos' attribute to be enabled in the lexer (DISABLED by default).
     *
     * @return int File end position (or -1 if not available)
     */
    public function getEndFilePos(): int {
        return $this->attributes['endFilePos'] ?? -1;
    }

    /**
     * Gets all comments directly preceding this node.
	 * 获取直接在此节点前面的所有注释。
     *
     * The comments are also available through the "comments" attribute.
     *
     * @return Comment[]
     */
    public function getComments(): array {
        return $this->attributes['comments'] ?? [];
    }

    /**
     * Gets the doc comment of the node.
	 * 获取节点的文档注释。
     *
     * @return null|Comment\Doc Doc comment object or null
     */
    public function getDocComment(): ?Comment\Doc {
        $comments = $this->getComments();
        for ($i = count($comments) - 1; $i >= 0; $i--) {
            $comment = $comments[$i];
            if ($comment instanceof Comment\Doc) {
                return $comment;
            }
        }

        return null;
    }

    /**
     * Sets the doc comment of the node.
	 * 设置节点的文档注释。
     *
     * This will either replace an existing doc comment or add it to the comments array.
     *
     * @param Comment\Doc $docComment Doc comment to set
     */
    public function setDocComment(Comment\Doc $docComment): void {
        $comments = $this->getComments();
        for ($i = count($comments) - 1; $i >= 0; $i--) {
            if ($comments[$i] instanceof Comment\Doc) {
                // Replace existing doc comment.
                $comments[$i] = $docComment;
                $this->setAttribute('comments', $comments);
                return;
            }
        }

        // Append new doc comment.
        $comments[] = $docComment;
        $this->setAttribute('comments', $comments);
    }

    public function setAttribute(string $key, $value): void {
        $this->attributes[$key] = $value;
    }

    public function hasAttribute(string $key): bool {
        return array_key_exists($key, $this->attributes);
    }

    public function getAttribute(string $key, $default = null) {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        return $default;
    }

    public function getAttributes(): array {
        return $this->attributes;
    }

    public function setAttributes(array $attributes): void {
        $this->attributes = $attributes;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array {
        return ['nodeType' => $this->getType()] + get_object_vars($this);
    }
}
