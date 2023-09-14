<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

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
        Response::macro('success', function ($data, $message , $status_code) {
            return Response::json([
                'success'  => true,
                'data' => $data,
                'message' => $message,
            ], $status_code);
        });
        Response::macro('error', function ($message, $status_code) {
            return Response::json([
                'success'  => false,
                'message' => $message,
            ], $status_code);
        });
    }
}
