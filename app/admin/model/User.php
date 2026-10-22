<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\Model;

class User extends Model
{
    /** 状态：正常 */
    public const STATUS_NORMAL = 1;
    /** 状态：禁用 */
    public const STATUS_DISABLE = 0;
    /** 是否管理员：是 */
    public const IS_ADMIN_YES = 1;

    /** 自动写入 create_time / update_time（Unix 时间戳） */
    protected $autoWriteTimestamp = true;

    /** 转数组或 JSON 时不输出密码哈希 */
    protected $hidden = ['password'];

    /**
     * 按用户名取一条用户记录，没有则返回 null
     */
    public function findByUsername(string $username): ?static
    {
        return $this->where('username', $username)->find();
    }

    /**
     * @param User $user 用户
     * @param string $ip 登陆ip
     * @return bool
     */
    public function login(self $user, string $ip): bool
    {
        $user->last_login_time = time();
        $user->last_login_ip   = $ip;

        return  $user->save();
    }
}
