<?php namespace Lialosiu\SocialiteChina\Two;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Laravel\Socialite\Two\User;
use Lialosiu\SocialiteChina\Exception\WeixinOAuthException;
use Mockery\CountValidator\Exception;

class WeixinProvider extends AbstractProvider implements ProviderInterface
{

    protected $openId = '';

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['snsapi_login'];

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://open.weixin.qq.com/connect/qrconnect', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCodeFields($state = null)
    {
        $fields = [
            'appid'         => $this->clientId, 'redirect_uri' => $this->redirectUrl,
            'scope'         => $this->formatScopes($this->scopes, $this->scopeSeparator),
            'response_type' => 'code',
        ];

        if ($this->usesState()) {
            $fields['state'] = $state;
        }

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://api.weixin.qq.com/sns/oauth2/access_token';
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

        $this->openId = $this->parseOpenId($response->getBody());

        return $this->parseAccessToken($response->getBody());
    }

    /**
     * {@inheritdoc}
     */
    public function user($token = null, $openId = null)
    {
        if (!$token)
            return parent::user();

        if ($openId) {
            $user = $this->mapUserToObject($this->getUserByTokenAndOpenId($token, $openId));
        } else {
            $user = $this->mapUserToObject($this->getUserByToken($token));
        }

        return $user->setToken($token);
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        try {
            $response = $this->getHttpClient()->get('https://api.weixin.qq.com/sns/userinfo', [
                'query' => [
                    'access_token' => $token,
                    'openid'       => $this->openId,
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
    protected function getUserByTokenAndOpenId($token, $openId)
    {
        try {
            $response = $this->getHttpClient()->get('https://api.weixin.qq.com/sns/userinfo', [
                'query' => [
                    'access_token' => $token,
                    'openid'       => $openId,
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
            'id'       => array_get($user, 'unionid'),
            'nickname' => array_get($user, 'nickname'),
            'name'     => array_get($user, 'nickname'),
            'email'    => array_get($user, 'email'),
            'avatar'   => array_get($user, 'headimgurl'),
        ]);
    }

    protected function getTokenFields($code)
    {
        return [
            'appid'      => $this->clientId,
            'secret'     => $this->clientSecret,
            'grant_type' => 'authorization_code',
            'code'       => $code,
        ];
    }

    private function getOpenId($token)
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
        if (!is_array($data) || isset($data['errcode'])) {
            $error     = array_get($data, 'errmsg');
            $errorCode = array_get($data, 'errcode');
            throw new WeixinOAuthException($error, $errorCode);
        }

        return $data;
    }

    protected function parseOpenId($body)
    {
        return json_decode($body, true)['openid'];
    }
}
