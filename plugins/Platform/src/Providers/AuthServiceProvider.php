<?php

namespace AcmeCorp\Platform\Providers;

use Nova\Auth\Contracts\Access\GateInterface as Gate;
use Nova\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = array(
        'AcmeCorp\Platform\Models\SomeModel' => 'AcmeCorp\Platform\Policies\ModelPolicy',
    );


    /**
     * Register any application authentication / authorization services.
     *
     * @param  Nova\Auth\Contracts\Access\GateInterface  $gate
     * @return void
     */
    public function boot(Gate $gate)
    {
        $this->registerPolicies($gate);

        //
    }
}
