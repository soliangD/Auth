<?php

namespace Yunhan\JAuth\Models;

use Illuminate\Database\Eloquent\Model;
use Yunhan\JAuth\Util\AuthUtil;

/**
 * Class Ticket
 * @property int $id
 * @property int $uid
 * @property string $token
 * @property string $ip
 * @property string $expiration
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @package JMD\Auth\Models
 */
class Ticket extends Model
{
    protected $guarded = [];

    protected $status_normal = 0;

    protected $status_del = 2;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(AuthUtil::getTicketTableName());
    }

    public function add($uid, $token, $guard, $exp)
    {
        $add = [
            'uid' => $uid,
            'token' => $token,
            'guard' => $guard,
            'status' => 0,
            'ip' => AuthUtil::request()->ip() ?: '',
            'expiration' => AuthUtil::currentTime() + $exp,
        ];
        $res = $this->create($add)->id;
        return $res ? $token : false;
    }

    public function del($token, $guard)
    {
        return static::where([
                'token' => $token,
                'guard' => $guard
            ])
            ->update(['status' => $this->status_del]) > 0;
    }

    public function get($token, $guard)
    {
        return static::where([
            'token' => $token,
            'status' => $this->status_normal,
            'guard' => $guard
        ])->first();
    }
}