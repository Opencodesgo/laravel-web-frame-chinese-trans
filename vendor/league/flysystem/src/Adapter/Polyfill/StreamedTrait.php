<?php
/**
 * League，Flysystem，适配器，Polyfill，流式特性
 */

namespace League\Flysystem\Adapter\Polyfill;

trait StreamedTrait
{
    use StreamedReadingTrait;
    use StreamedWritingTrait;
}
