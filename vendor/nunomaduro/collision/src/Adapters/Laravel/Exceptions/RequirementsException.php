<?php
/**
 * NunoMaduro，Collision，适配器，Laravel，异常，需求异常
 */

declare(strict_types=1);

namespace NunoMaduro\Collision\Adapters\Laravel\Exceptions;

use NunoMaduro\Collision\Contracts\RenderlessEditor;
use NunoMaduro\Collision\Contracts\RenderlessTrace;
use RuntimeException;

/**
 * @internal
 */
final class RequirementsException extends RuntimeException implements RenderlessEditor, RenderlessTrace
{
}
