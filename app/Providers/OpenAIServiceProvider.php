<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use OpenAI;

class OpenAIServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(\OpenAI\Client::class, function () {
            return OpenAI::client(env('OPENAI_API_KEY'));
        });
    }

    public function boot()
    {
        //
    }
}
