<?php namespace Lialosiu\SocialiteChina\Contracts;

interface Provider
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
     * @return \Lialosiu\SocialiteChina\Contracts\User
     */
    public function user($token = null);
}
