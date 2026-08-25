<?php declare(strict_types=1);

/**
 * PhpParser，节点，Stmt，Break_
 */

namespace PhpParser\Node\Stmt;

use PhpParser\Node;

class Break_ extends Node\Stmt {
    /** @var null|Node\Expr Number of loops to break */
    public ?Node\Expr $num;

    /**
     * Constructs a break node.
	 * 构造一个中断节点
     *
     * @param null|Node\Expr $num Number of loops to break
     * @param array<string, mixed> $attributes Additional attributes
     */
    public function __construct(?Node\Expr $num = null, array $attributes = []) {
        $this->attributes = $attributes;
        $this->num = $num;
    }

    public function getSubNodeNames(): array {
        return ['num'];
    }

    public function getType(): string {
        return 'Stmt_Break';
    }
}
