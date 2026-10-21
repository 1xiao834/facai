<?php
declare (strict_types = 1);

namespace app\admin\validate;

use think\Validate;

/**
 * 后台登录参数校验。只检查用户有没有按要求提交，不核对密码对不对。
 */
class Login extends Validate
{
    protected $rule = [
        'username'    => 'require|max:50',
        'password'    => 'require|max:255',
        'captcha'     => 'require|captcha',
    ];

    protected $message = [
        'username.require' => '请输入账号',
        'username.max'     => '账号不能超过50个字符',
        'password.require' => '请输入密码',
        'password.max'     => '密码不能超过255个字符',
        'captcha.require'  => '请输入验证码',
    ];

    protected $field = [
        'captcha' => '验证码',
    ];

    // 登录场景校验账号、密码和图形验证码
    protected $scene = [
        'login' => ['username', 'password', 'captcha'],
    ];
}
