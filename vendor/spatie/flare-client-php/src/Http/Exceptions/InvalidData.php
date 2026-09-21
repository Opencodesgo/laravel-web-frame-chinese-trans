<?php
/**
 * Spatie，FlareClient，Http，异常，无效数据
 */

namespace Spatie\FlareClient\Http\Exceptions;

use Spatie\FlareClient\Http\Response;

class InvalidData extends BadResponseCode
{
    public static function getMessageForResponse(Response $response): string
    {
        return 'Invalid data found';
    }
}
