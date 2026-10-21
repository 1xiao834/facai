<?php

use think\facade\Route;

// 获取验证码，不需要登录
Route::get('captcha', 'Login/captcha');
// 登录不挂中间件，否则还没有令牌就会被拦住
Route::post('login', 'Login/login');
// 退出必须先通过 Auth，确认令牌有效后再删掉该用户缓存的 JWT
Route::post('logout', 'Login/logout')->middleware(\app\admin\middleware\Auth::class);
