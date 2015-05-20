<?php namespace Lialosiu\SocialiteChina\Two;

use Guzzle\Common\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Stream\GuzzleStreamWrapper;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;
use Lialosiu\SocialiteChina\Exception\QqOAuthException;

class QqProvider extends AbstractProvider implements ProviderInterface
{
    private $openid = null;

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
        return $this->buildAuthUrlFromBase('https://graph.qq.com/oauth2.0/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://graph.qq.com/oauth2.0/token';
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken($code)
    {
        try {
            $response = $this->getHttpClient()->get($this->getTokenUrl(), [
                'query' => $this->getTokenFields($code),
            ]);
        } catch (RequestException $e) {
            $this->checkError($this->jsonp_decode($e->getResponse()->getBody(), true));
            throw $e;
        }

        $this->checkError($this->jsonp_decode($response->getBody(), true));

        return $this->parseAccessToken($response->getBody());
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $openid = $this->getOpenid($token);

        try {
            $response = $this->getHttpClient()->get('https://graph.qq.com/user/get_user_info', [
                'query' => [
                    'access_token'       => $token,
                    'oauth_consumer_key' => $this->clientId,
                    'openid'             => $openid,
                ],
            ]);
        } catch (RequestException $e) {
            $this->checkError($this->jsonp_decode($e->getResponse()->getBody(), true));
            throw $e;
        }

        $data = $this->checkError($this->jsonp_decode($response->getBody(), true));

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id'       => $this->openid,
            'nickname' => array_get($user, 'nickname'),
            'name'     => array_get($user, 'nickname'),
            'email'    => array_get($user, 'email'),
            'avatar'   => array_get($user, 'figureurl_2'),
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

    private function getOpenid($token)
    {
        try {
            $response = $this->getHttpClient()->get('https://graph.qq.com/oauth2.0/me', [
                'query' => [
                    'access_token' => $token,
                ],
            ]);
        } catch (RequestException $e) {
            $this->checkError($this->jsonp_decode($e->getResponse()->getBody(), true));
            throw $e;
        }

        $data = $this->checkError($this->jsonp_decode($response->getBody(), true));

        $openid = array_get($data, 'openid');

        $this->openid = $openid;

        return array_get($data, 'openid');
    }

    private function checkError($data)
    {
        if (isset($data['error'])) {
            $errorCode = array_get($data, 'error');
            $error     = array_get($data, 'error_description');
            throw new QqOAuthException($error, $errorCode);
        }

        return $data;
    }

    protected function parseAccessToken($body)
    {
        $data = explode('&', $body);
        foreach ($data as $thisData) {
            $tmp = explode('=', $thisData);
            if ($tmp[0] == 'access_token') {
                return $tmp[1];
            }
        }
        return '';
    }

    private function jsonp_decode($jsonp, $assoc = false)
    {
        $jsonp = $jsonp->__toString();
        if ($jsonp[0] !== '[' && $jsonp[0] !== '{') {
            $jsonp = substr($jsonp, strpos($jsonp, '('));
        }
        return json_decode(trim($jsonp, "();\t\n\r\0\x0B"), $assoc);
    }
}
