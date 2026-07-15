<?php
/**
 * 门面，Ignition，Http，请求，共享报告请求
 */

namespace Facade\Ignition\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShareReportRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'report' => 'required',
            'tabs' => 'required|array|min:1',
            'lineSelection' => [],
        ];
    }
}
