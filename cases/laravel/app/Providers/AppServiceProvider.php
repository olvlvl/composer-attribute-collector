<?php

namespace App\Providers;

use App\Attributes\SampleAttribute;
use Illuminate\Support\ServiceProvider;

#[SampleAttribute]
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
