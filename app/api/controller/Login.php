<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\BaseController;
use app\admin\model\User;

class Login extends BaseController
{
    public function login()
    {
        $username = (string) $this->request->post('username', '');
        $password = (string) $this->request->post('password', '');

        $user = (new User())->findByUsername($username);
        if (!$user || !password_verify($password, (string) $user->password)) {
            return json(['code' => 1, 'msg' => '账号或密码错误', 'data' => null]);
        }

        $token = bin2hex(random_bytes(32));
        cache('login_token:' . $token, (int) $user->id, 7200);

        return json([
            'code' => 0,
            'msg'  => '登录成功',
            'data' => [
                'token' => $token,
                'user'  => [
                    'id'       => $user->id,
                    'username' => $user->username,
                ],
            ],
        ]);
    }

    public function me()
    {
        $user = User::find($this->request->userId);
        if (!$user) {
            return json(['code' => 1, 'msg' => '用户不存在', 'data' => null]);
        }

        return json([
            'code' => 0,
            'msg'  => 'ok',
            'data' => [
                'id'       => $user->id,
                'username' => $user->username,
            ],
        ]);
    }
}
