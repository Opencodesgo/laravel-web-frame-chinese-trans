<?php
/**
 * Egulias，Flare Client，Http，异常，不良反应
 */

namespace Facade\FlareClient\Http\Exceptions;

use Facade\FlareClient\Http\Response;

class NotFound extends BadResponseCode
{
    public static function getMessageForResponse(Response $response)
    {
        return 'Not found';
    }
}
