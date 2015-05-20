<?php namespace Lialosiu\SocialiteChina\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Lialosiu\SocialiteChina\SocialiteChinaManager
 */
class Socialite extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Lialosiu\SocialiteChina\Contracts\Factory';
    }
}
