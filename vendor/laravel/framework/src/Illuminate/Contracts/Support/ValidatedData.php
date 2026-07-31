<?php
/**
 * Illuminate，契约，支持，验证数据
 */

namespace Illuminate\Contracts\Support;

use ArrayAccess;
use IteratorAggregate;

interface ValidatedData extends Arrayable, ArrayAccess, IteratorAggregate
{
    //
}
