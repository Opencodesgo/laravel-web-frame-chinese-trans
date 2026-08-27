<?php
/**
 * App, 提供者, 认证服务提供者
 */

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
	 * 应用程序的模型到策略映射
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
	 * 注册任何身份验证/授权服务
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
