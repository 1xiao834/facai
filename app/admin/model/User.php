<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\Model;

class User extends Model
{
    /**
     * 按用户名取一条用户记录，没有则返回 null
     */
    public function findByUsername(string $username): ?static
    {
        return $this->where('username', $username)->find();
    }
}
