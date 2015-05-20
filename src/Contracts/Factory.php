<?php namespace Lialosiu\SocialiteChina\Contracts;

interface Factory
{

    /**
     * Get an OAuth provider implementation.
     *
     * @param  string  $driver
     * @return \Lialosiu\SocialiteChina\Contracts\Provider
     */
    public function driver($driver = null);
}
