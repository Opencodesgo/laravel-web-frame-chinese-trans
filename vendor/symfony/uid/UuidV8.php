<?php
/**
 * Symfony，Component，Uid，Uuid V8
 */

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Uid;

/**
 * A v8 UUID has no explicit requirements except embedding its version + variant bits.
 * v8 UUID除了嵌入其版本+变体位外，没有明确的要求。
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class UuidV8 extends Uuid
{
    protected const TYPE = 8;

    public function __construct(string $uuid)
    {
        parent::__construct($uuid, true);
    }
}
