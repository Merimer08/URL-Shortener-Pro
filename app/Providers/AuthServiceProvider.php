<?php

namespace App\Providers;

use App\Models\Link;
use App\Policies\LinkPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Link::class => LinkPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
