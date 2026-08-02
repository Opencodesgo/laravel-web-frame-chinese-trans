<?php
/**
 * Facade，FlareClient，Http，异常，无效数据
 */

namespace Facade\FlareClient\Http\Exceptions;

use Facade\FlareClient\Http\Response;

class InvalidData extends BadResponseCode
{
    public static function getMessageForResponse(Response $response)
    {
        return 'Invalid data found';
    }
}
