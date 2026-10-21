<?php
declare (strict_types = 1);

namespace app\admin\service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * 后台 JWT。
 * 令牌本身带签名和过期时间。是否仍有效，看缓存里该用户保存的那一整段 JWT 是否还在。
 */
class JwtToken
{
    // 有效期，单位秒。和登录时 Cache::set 的第三个参数一致
    private const TTL = 86400;

    /**
     * 签发令牌。缓存由登录控制器按用户 id 写入
     */
    public static function issue(int $userId): string
    {
        $key = env('JWT_SECRET');

        // jti 是这枚令牌的编号，写进 JWT 里，缓存不靠它
        $jti = bin2hex(random_bytes(16));

        $token = JWT::encode([
            'iss' => 'admin',             // 签发方
            'iat' => time(),                // 签发时间
            'exp' => time() + self::TTL,    // 过期时间
            'jti' => $jti,                // 令牌编号
            'uid' => $userId,             // 用户 id
        ], $key, 'HS256');

        return $token;
    }

    /**
     * 校验令牌。签名不对、已过期，或缓存里没有这整段 JWT，都返回 null
     */
    public static function parse(string $token): ?array
    {
        try {
            // 用 .env 里的 JWT_SECRET 验签，并检查 exp
            $key = env('JWT_SECRET');
            $payload = JWT::decode($token, new Key($key, 'HS256'));
        } catch (\Throwable $e) {
            return null;
        }

        $jti = (string) ($payload->jti ?? '');
        $userId = (int) ($payload->uid ?? 0);
        if ($jti === '' || $userId <= 0) {
            return null;
        }

        // 登录时按 admin_user_{用户id}_token 存的是整段 JWT。对不上说明已退出，或被后一次登录覆盖
        $cached = cache('admin_user_' . $userId . '_token');
        if (!is_string($cached) || !hash_equals($cached, $token)) {
            return null;
        }

        return ['jti' => $jti, 'uid' => $userId];
    }
}
