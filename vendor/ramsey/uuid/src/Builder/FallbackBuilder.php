<?php
/**
 * Ramsey，Uuid，构建器，构建器集合
 */

/**
 * This file is part of the ramsey/uuid library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Ramsey <ben@benramsey.com>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace Ramsey\Uuid\Builder;

use Ramsey\Uuid\Codec\CodecInterface;
use Ramsey\Uuid\Exception\BuilderNotFoundException;
use Ramsey\Uuid\Exception\UnableToBuildUuidException;
use Ramsey\Uuid\UuidInterface;

/**
 * FallbackBuilder builds a UUID by stepping through a list of UUID builders until a UUID can be constructed without exceptions
 * FallbackBuilder 通过逐步遍历UUID构建器列表来构建UUID，直到可以无异常地构建UUID为止
 *
 * @immutable
 */
class FallbackBuilder implements UuidBuilderInterface
{
    /**
     * @param iterable<UuidBuilderInterface> $builders An array of UUID builders
     */
    public function __construct(private iterable $builders)
    {
    }

    /**
     * Builds and returns a UuidInterface instance using the first builder that succeeds
	 * 使用第一个成功的构建器构建并返回一个UuidInterface实例
     *
     * @param CodecInterface $codec The codec to use for building this instance
     * @param string $bytes The byte string from which to construct a UUID
     *
     * @return UuidInterface an instance of a UUID object
     *
     * @pure
     */
    public function build(CodecInterface $codec, string $bytes): UuidInterface
    {
        $lastBuilderException = null;

        foreach ($this->builders as $builder) {
            try {
                return $builder->build($codec, $bytes);
            } catch (UnableToBuildUuidException $exception) {
                $lastBuilderException = $exception;

                continue;
            }
        }

        throw new BuilderNotFoundException(
            'Could not find a suitable builder for the provided codec and fields',
            0,
            $lastBuilderException,
        );
    }
}
