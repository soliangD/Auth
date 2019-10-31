<?php

namespace JMD\Auth;

use Illuminate\Support\ServiceProvider;
use JMD\Auth\Exceptions\ExpiredException;
use JMD\Auth\Exceptions\SignatureTokenException;
use JMD\Auth\Exceptions\UnauthorizedException;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     * @suppress PhanTypeArraySuspicious
     * @return void
     */
    public function boot()
    {
        // 默认报错
        $this->app['auth']->viaRequest('default', function ($request) {
            throw new UnauthorizedException('未进行身份认证');
        });
        // 获取用户
        $this->app['auth']->viaRequest('JAuth', function ($request) {
            try {
                return AuthBase::getUser();
            } catch (SignatureTokenException $e) {
                return null;
            } catch (ExpiredException $e) {
                return null;
            }
        });
    }
}
