<?php
declare (strict_types = 1);

namespace app\admin\middleware;

use app\admin\service\JwtToken;
use Closure;
use think\Request;
use think\Response;

/**
 * 后台登录校验。挂到需要登录的路由上，登录、验证码接口不要挂。
 * 写法参考 newwx AuthMiddleware：先拆 Authorization，再验 token，最后把用户信息写入中间件。
 */
class Auth
{
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');
        if (!$authHeader) {
            return json(['code' => 401, 'msg' => '请先登录', 'data' => null], 401);
        }

        // 请求头必须是 Authorization: Bearer <JWT>
        if (strpos($authHeader, 'Bearer ') !== 0) {
            return json(['code' => 401, 'msg' => '无效的验证头格式', 'data' => null], 401);
        }

        // 去掉前面的 "Bearer "（7 个字符），剩下才是令牌
        $token = substr($authHeader, 7);
        if ($token === '') {
            return json(['code' => 401, 'msg' => 'Token 不能为空', 'data' => null], 401);
        }

        // 验签、过期、以及缓存里 admin_user_{id}_token 是否还是这一整段，都在 parse 里做
        $payload = JwtToken::parse($token);
        if (!$payload) {
            return json(['code' => 401, 'msg' => '无效的Token', 'data' => null], 401);
        }

        // 显式写入中间件数据，后面用 $request->middleware('userId') 取（退出接口就是这样取的）
        $request->withMiddleware([
            'userId' => $payload['uid'],
            'jti'    => $payload['jti'],
        ]);

        return $next($request);
    }
}
