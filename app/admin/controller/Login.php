<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\BaseController;
use app\admin\model\User;
use app\admin\service\JwtToken;
use app\Request;
use think\exception\ValidateException;
use think\facade\Cache;

class Login extends BaseController
{
    /**
     * 图形验证码。由 think-captcha 画图，答案的哈希写在 Session 里
     */
    public function captcha()
    {
        return captcha();
    }

    /**
     * 登录：核对验证码和账号密码，通过后签发 JWT
     */
    public function login(Request $request)
    {
        $params = $request->post();
        // 用登录场景校验，字段在验证器的 scene 里，这里不用再列
        try {
            validate(\app\admin\validate\Login::class)
                ->scene('login')
                ->check($params);
        } catch (ValidateException $e) {
            $message = $e->getError();
            return $this->error(is_array($message) ? (string) reset($message) : (string) $message);
        }

        $username = (string) ($params['username'] ?? '');
        $password = (string) ($params['password'] ?? '');

        $userModel = new User();
        $user      = $userModel->findByUsername($username);
        // password 字段存的是 password_hash 的结果，不能直接比对明文
        if (!$user || !password_verify($password, (string) $user->password)) {
            return $this->error('账号或密码错误');
        }

        // 禁用账号不能登录
        if ((int) $user->status !== User::STATUS_NORMAL) {
            return $this->error('账号已被禁用');
        }
        // 后台只允许管理员进入
        if ((int) $user->is_admin !== User::IS_ADMIN_YES) {
            return $this->error('无后台登录权限');
        }

        // 返回给前端的 token 是整段 JWT，之后放到 Authorization 请求头里
        $token = JwtToken::issue((int) $user->id);
        // 按用户 id 保存这一整段令牌，有效期一天。后一次登录会覆盖上一次
        Cache::set('admin_user_' . $user->id . '_token', $token, 86400);

        // 更新最后登录时间和 IP（写在模型里，控制器不直接改字段）
        if (!$userModel->login($user, (string) $request->ip())) {
            return $this->error('登录失败');
        }

        return $this->success([
            'token' => $token,
            'user'  => [
                'id'       => $user->id,
                'username' => $user->username,
                'nickname' => $user->nickname,
                'avatar'   => $user->avatar,
            ],
        ], '登录成功');
    }

    /**
     * 退出：删掉该用户缓存在 admin_user_{id}_token 里的 JWT。调用前要先经过 Auth 中间件
     */
    public function logout(Request $request)
    {
        $user = $request->middleware('userId');

        // userId 由 Auth 中间件从令牌里取出，不从提交内容里取
        Cache::delete('admin_user_' . $user . '_token');

        return $this->success(null, '退出成功');
    }
}
