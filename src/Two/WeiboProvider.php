<?php namespace Lialosiu\SocialiteChina\Two;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Laravel\Socialite\Two\User;
use Lialosiu\SocialiteChina\Exception\WeiboOAuthException;
use Mockery\CountValidator\Exception;

class WeiboProvider extends AbstractProvider implements ProviderInterface
{

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['user:email'];

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://api.weibo.com/oauth2/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://api.weibo.com/oauth2/access_token';
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken($code)
    {
        $postKey = (version_compare(ClientInterface::VERSION, '6') === 1) ? 'form_params' : 'body';

        try {
            $response = $this->getHttpClient()->post($this->getTokenUrl(), [
                $postKey => $this->getTokenFields($code),
            ]);
        } catch (RequestException $e) {
            if ($e->hasResponse())
                $this->decode($e->getResponse()->getBody());
            throw $e;
        }

        $this->decode($response->getBody());

        return $this->parseAccessToken($response->getBody());
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $uid = $this->getUid($token);

        try {
            $response = $this->getHttpClient()->get('https://api.weibo.com/2/users/show.json', [
                'query' => [
                    'access_token' => $token,
                    'uid'          => $uid,
                ],
            ]);
        } catch (RequestException $e) {
            if ($e->hasResponse())
                $this->decode($e->getResponse()->getBody());
            throw $e;
        }

        $data = $this->decode($response->getBody());

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id'       => array_get($user, 'id'),
            'nickname' => array_get($user, 'screen_name'),
            'name'     => array_get($user, 'name'),
            'email'    => array_get($user, 'email'),
            'avatar'   => array_get($user, 'avatar_hd'),
        ]);
    }

    protected function getTokenFields($code)
    {
        return [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $this->redirectUrl
        ];
    }

    private function getUid($token)
    {
        $postKey = (version_compare(ClientInterface::VERSION, '6') === 1) ? 'form_params' : 'body';

        try {
            $response = $this->getHttpClient()->post('https://api.weibo.com/oauth2/get_token_info', [
                $postKey => [
                    'access_token' => $token,
                ],
            ]);
        } catch (RequestException $e) {
            if ($e->hasResponse())
                $this->decode($e->getResponse()->getBody());
            throw $e;
        }

        $data = $this->decode($response->getBody());

        return array_get($data, 'uid');
    }

    private function decode($data)
    {
        try {
            $data = json_decode($data, true);
        } catch (Exception $e) {

        }
        if (!is_array($data) || isset($data['error_code'])) {
            $error     = array_get($data, 'error');
            $errorCode = array_get($data, 'error_code');
            throw new WeiboOAuthException($error, $errorCode);
        }

        return $data;
    }
}
