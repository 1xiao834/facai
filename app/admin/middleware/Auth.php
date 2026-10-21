<?php
declare (strict_types = 1);

namespace app\admin\middleware;

use app\admin\service\JwtToken;
use Closure;
use think\Request;
use think\Response;

/**
 * 后台登录校验。挂到需要登录的路由上，登录接口本身不要挂。
 */
class Auth
{
    public function handle(Request $request, Closure $next): Response
    {
        // 请求头格式：Authorization: Bearer <JWT>
        $header = (string) $request->header('authorization', '');
        $token = '';
        // Bearer 后面那一段才是令牌，括号里的内容进 $match[1]
        if (preg_match('/Bearer\s+(\S+)/i', $header, $match)) {
            $token = $match[1];
        }

        // 验签失败、过期，或缓存里的令牌已被退出删掉，都视为未登录
        $payload = $token !== '' ? JwtToken::parse($token) : null;
        if (!$payload) {
            return json(['code' => 401, 'msg' => '请先登录', 'data' => null], 401);
        }

        // 交给后面的控制器。退出接口用 userId 删除缓存里的令牌
        $request->userId = $payload['uid'];
        $request->jti = $payload['jti'];

        return $next($request);
    }
}
