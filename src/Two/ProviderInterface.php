<?php namespace Lialosiu\SocialiteChina\Two;

interface ProviderInterface
{

    /**
     * Redirect the user to the authentication page for the provider.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect();

    /**
     * Get the User instance for the authenticated user.
     *
     * @param string $token
     * @return \Laravel\Socialite\Two\User
     */
    public function user($token = null);
}
