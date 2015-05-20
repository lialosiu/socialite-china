# lialosiu/socialite-china


## Introduction

Laravel Socialite OAuth authentication for China.

## Usage

```
composer require "lialosiu/socialite-china:~1.0"
```

Add provider ```'Lialosiu\SocialiteChina\SocialiteChinaServiceProvider'``` in your ```config/app.php```

Add config in ```config/services.php``` :

```
'weibo'    => [
    'client_id'     => env('WEIBO_APP_KEY', ''),
    'client_secret' => env('WEIBO_APP_SECRET', ''),
    'redirect'      => env('WEIBO_CALLBACK_URL', ''),
],

'qq'       => [
    'client_id'     => env('QQ_APP_KEY', ''),
    'client_secret' => env('QQ_APP_SECRET', ''),
    'redirect'      => env('QQ_CALLBACK_URL', ''),
],
```

Add env in ```.env``` :

```
WEIBO_APP_KEY=YourWeiboAppKey
WEIBO_APP_SECRET=YourWeiboAppSecret
WEIBO_CALLBACK_URL=YourWeiboCallBackUrl

QQ_APP_KEY=YourQqAppKey
QQ_APP_SECRET=YourQqAppKey
QQ_CALLBACK_URL=YourQqCallBackUrl
```

## Documentation

Same with the [laravel/socialite](http://laravel.com/docs/5.0/authentication#social-authentication)

## Require

[laravel/socialite](https://github.com/laravel/socialite)

## License

[MIT license](http://opensource.org/licenses/MIT)
