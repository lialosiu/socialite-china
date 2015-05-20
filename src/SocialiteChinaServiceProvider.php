<?php namespace Lialosiu\SocialiteChina;

use Illuminate\Support\ServiceProvider;

class SocialiteChinaServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bindShared('Lialosiu\SocialiteChina\Contracts\Factory', function ($app) {
            return new SocialiteChinaManager($app);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['Lialosiu\SocialiteChina\Contracts\Factory'];
    }
}
