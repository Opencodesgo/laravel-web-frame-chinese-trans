<?php
/**
 * Facade，FlareClient，上下文，控制台上下文
 */

namespace Facade\FlareClient\Context;

class ConsoleContext implements ContextInterface
{
    /** @var array */
    private $arguments = [];

    public function __construct(array $arguments = [])
    {
        $this->arguments = $arguments;
    }

    public function toArray(): array
    {
        return [
            'arguments' => $this->arguments,
        ];
    }
}
