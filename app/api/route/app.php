<?php

use think\facade\Route;

Route::group(function () {
    Route::post('login', 'Login/login');
    Route::get('me', 'Login/me')->middleware(\app\api\middleware\Auth::class);
})->allowCrossDomain();
