<?php namespace Lialosiu\SocialiteChina;

use Illuminate\Support\Manager;
use InvalidArgumentException;

class SocialiteChinaManager extends Manager implements Contracts\Factory
{

    /**
     * Get a driver instance.
     *
     * @param  string $driver
     * @return mixed
     */
    public function with($driver)
    {
        return $this->driver($driver);
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Laravel\Socialite\One\AbstractProvider
     */
    protected function createWeiboDriver()
    {
        $config = $this->app['config']['services.weibo'];

        return $this->buildProvider(
            'Lialosiu\SocialiteChina\Two\WeiboProvider', $config
        );
    }

    /**
     * Build an OAuth 2 provider instance.
     *
     * @param  string $provider
     * @param  array $config
     * @return \Laravel\Socialite\Two\AbstractProvider
     */
    public function buildProvider($provider, $config)
    {
        return new $provider(
            $this->app['request'], $config['client_id'],
            $config['client_secret'], $config['redirect']
        );
    }

    /**
     * Format the Twitter server configuration.
     *
     * @param  array $config
     * @return array
     */
    public function formatConfig(array $config)
    {
        return [
            'identifier'   => $config['client_id'],
            'secret'       => $config['client_secret'],
            'callback_uri' => $config['redirect'],
        ];
    }

    /**
     * Get the default driver name.
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        throw new InvalidArgumentException("No Socialite driver was specified.");
    }
}
