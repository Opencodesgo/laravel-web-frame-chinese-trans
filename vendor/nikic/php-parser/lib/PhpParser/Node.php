<?php declare(strict_types=1);

/**
 * PhpParser，节点
 */

namespace PhpParser;

interface Node {
    /**
     * Gets the type of the node.
	 * 获取节点的类型
     *
     * @psalm-return non-empty-string
     * @return string Type of the node
     */
    public function getType(): string;

    /**
     * Gets the names of the sub nodes.
	 * 获取子节点的名称
     *
     * @return string[] Names of sub nodes
     */
    public function getSubNodeNames(): array;

    /**
     * Gets line the node started in (alias of getStartLine).
	 * 获取开始所在节点的行（getStartLine的别名）
     *
     * @return int Start line (or -1 if not available)
     * @phpstan-return -1|positive-int
     *
     * @deprecated Use getStartLine() instead
     */
    public function getLine(): int;

    /**
     * Gets line the node started in.
	 * 获取节点开始所在的行
     *
     * Requires the 'startLine' attribute to be enabled in the lexer (enabled by default).
     *
     * @return int Start line (or -1 if not available)
     * @phpstan-return -1|positive-int
     */
    public function getStartLine(): int;

    /**
     * Gets the line the node ended in.
	 * 获取节点结束的行。
     *
     * Requires the 'endLine' attribute to be enabled in the lexer (enabled by default).
     *
     * @return int End line (or -1 if not available)
     * @phpstan-return -1|positive-int
     */
    public function getEndLine(): int;

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
    public function getStartTokenPos(): int;

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
    public function getEndTokenPos(): int;

    /**
     * Gets the file offset of the first character that is part of this node.
	 * 获取作为此节点一部分的第一个字符的文件偏移量。
     *
     * Requires the 'startFilePos' attribute to be enabled in the lexer (DISABLED by default).
     *
     * @return int File start position (or -1 if not available)
     */
    public function getStartFilePos(): int;

    /**
     * Gets the file offset of the last character that is part of this node.
	 * 获取作为该节点一部分的最后一个字符的文件偏移量。
     *
     * Requires the 'endFilePos' attribute to be enabled in the lexer (DISABLED by default).
     *
     * @return int File end position (or -1 if not available)
     */
    public function getEndFilePos(): int;

    /**
     * Gets all comments directly preceding this node.
	 * 获取直接在此节点前面的所有注释。
     *
     * The comments are also available through the "comments" attribute.
     *
     * @return Comment[]
     */
    public function getComments(): array;

    /**
     * Gets the doc comment of the node.
	 * 获取节点的文档注释。
     *
     * @return null|Comment\Doc Doc comment object or null
     */
    public function getDocComment(): ?Comment\Doc;

    /**
     * Sets the doc comment of the node.
	 * 设置节点的文档注释。
     *
     * This will either replace an existing doc comment or add it to the comments array.
     *
     * @param Comment\Doc $docComment Doc comment to set
     */
    public function setDocComment(Comment\Doc $docComment): void;

    /**
     * Sets an attribute on a node.
	 * 设置节点上的属性
     *
     * @param mixed $value
     */
    public function setAttribute(string $key, $value): void;

    /**
     * Returns whether an attribute exists.
	 * 返回属性是否存在
     */
    public function hasAttribute(string $key): bool;

    /**
     * Returns the value of an attribute.
	 * 返回属性的值
     *
     * @param mixed $default
     *
     * @return mixed
     */
    public function getAttribute(string $key, $default = null);

    /**
     * Returns all the attributes of this node.
	 * 返回该节点的所有属性
     *
     * @return array<string, mixed>
     */
    public function getAttributes(): array;

    /**
     * Replaces all the attributes of this node.
	 * 替换该节点的所有属性
     *
     * @param array<string, mixed> $attributes
     */
    public function setAttributes(array $attributes): void;
}
