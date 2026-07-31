<?php
/**
 * Illuminate，契约，队列，在处理之前应该是唯一
 */

namespace Illuminate\Contracts\Queue;

interface ShouldBeUniqueUntilProcessing extends ShouldBeUnique
{
    //
}
