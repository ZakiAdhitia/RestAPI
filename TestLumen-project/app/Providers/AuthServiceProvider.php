<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            if ($request->header('api_token')) { // Mengecek apakah API yang dikirim memiliki headers dengan key api_token
                                                // jika iya, maka akan di return berupa data user berdasarkan kolom token yang mana values 
                                                # dari tokennya disamakan dengan value di headers dengan key api_token 
                return User::where('token', $request->header('api_token'))->first();
            }
        });
    }
}
