<?php
/**
 * Spatie，LaravelIgnition，Http，控制器，执行解决方案控制器
 */

namespace Spatie\LaravelIgnition\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Spatie\Ignition\Contracts\SolutionProviderRepository;
use Spatie\LaravelIgnition\Exceptions\CannotExecuteSolutionForNonLocalIp;
use Spatie\LaravelIgnition\Http\Requests\ExecuteSolutionRequest;
use Spatie\LaravelIgnition\Support\RunnableSolutionsGuard;

class ExecuteSolutionController
{
    use ValidatesRequests;

    public function __invoke(
        ExecuteSolutionRequest $request,
        SolutionProviderRepository $solutionProviderRepository
    ) {
        $this
            ->ensureRunnableSolutionsEnabled()
            ->ensureLocalRequest();

        $solution = $request->getRunnableSolution();

        $solution->run($request->get('parameters', []));

        return response()->noContent();
    }

    public function ensureRunnableSolutionsEnabled(): self
    {
        // Should already be checked in middleware but we want to be 100% certain.
		// 应该已经在中间件中检查，但我们想要100%确定。
        abort_unless(RunnableSolutionsGuard::check(), 400);

        return $this;
    }

    public function ensureLocalRequest(): self
    {
        $ipIsPublic = filter_var(
            request()->ip(),
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );

        if ($ipIsPublic) {
            throw CannotExecuteSolutionForNonLocalIp::make();
        }

        return $this;
    }
}
