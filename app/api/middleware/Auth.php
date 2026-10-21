<?php
declare (strict_types = 1);

namespace app\api\middleware;

use Closure;
use think\Request;
use think\Response;

class Auth
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = (string) $request->header('authorization', '');
        $token = '';
        if (preg_match('/Bearer\s+(\S+)/i', $header, $match)) {
            $token = $match[1];
        }

        $userId = $token !== '' ? cache('login_token:' . $token) : null;
        if (!$userId) {
            return json(['code' => 401, 'msg' => '请先登录', 'data' => null], 401);
        }

        $request->userId = (int) $userId;

        return $next($request);
    }
}
