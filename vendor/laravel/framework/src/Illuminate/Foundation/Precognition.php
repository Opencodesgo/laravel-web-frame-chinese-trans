<?php
/**
 * Illuminate，基础，预知
 */

namespace Illuminate\Foundation;

class Precognition
{
    /**
     * Get the "after" validation hook that can be used for precognition requests.
	 * 获取可用于预知请求的"after"验证钩子
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Closure
     */
    public static function afterValidationHook($request)
    {
        return function ($validator) use ($request) {
            if ($validator->messages()->isEmpty() && $request->headers->has('Precognition-Validate-Only')) {
                abort(204);
            }
        };
    }
}
