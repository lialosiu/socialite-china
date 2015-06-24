<?php namespace Lialosiu\SocialiteChina\Two;

use Lialosiu\SocialiteChina\Contracts\Provider as ProviderContract;

abstract class AbstractProvider extends \Laravel\Socialite\Two\AbstractProvider implements ProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function user($token = null)
    {
        if (!$token)
            return parent::user();

        $user = $this->mapUserToObject($this->getUserByToken($token));

        return $user->setToken($token);
    }
}
