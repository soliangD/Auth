<?php

namespace JMD\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use JMD\Auth\AuthBase;
use JMD\Auth\Exceptions\SystemException;
use JMD\Auth\Exceptions\UnauthorizedException;
use JMD\Auth\Util\AuthUtil;

class JAuthMiddleware
{
    // 免认证白名单
    protected $whitelist = [
    ];

    public function handle(Request $request, Closure $next, $guard = null, $needLogin = null)
    {
        if (!AuthBase::guardNameIsValid($guard)) {
            throw new SystemException('Middleware 无效 Auth Guard 传参');
        }

        AuthUtil::setCurrentGuard($guard);

        // 中间件传参控制是否需要验证登录
        if (empty($needLogin)) {
            return $next($request);
        }

        // 白名单
        if (in_array($request->path(), $this->whitelist)) {
            return $next($request);
        }

        // 检查是否登录
        if (Auth::guest()) {
            throw new UnauthorizedException('未登录');
        }

        return $next($request);
    }
}
