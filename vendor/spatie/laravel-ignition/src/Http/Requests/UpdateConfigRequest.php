<?php
/**
 * Spatie，LaravelIgnition，Http，请求，更新配置请求
 */

namespace Spatie\LaravelIgnition\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConfigRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'theme' => ['required',  Rule::in(['light', 'dark', 'auto'])],
            'editor' => ['required'],
            'hide_solutions' => ['required', 'boolean'],
        ];
    }
}
