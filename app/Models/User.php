<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
//Authenticatable(认证) 是授权相关功能的引用
class User extends Authenticatable
{
//Notifiable（依法须上报的） 是消息通知相关功能引用
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
//可填充
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function gravatar($size = '100')
    {
        $hash = md5(strtolower(trim($this->attributes['email'])));
        return "https://secure.gravatar.com/avatar/$hash?s=$size";
    }

    public static function boot()
    {
//boot 方法会在用户模型类完成初始化之后进行加载，因此我们对事件的监听需要放在该方法中
        parent::boot();
//creating 用于监听模型被创建之前的事件
        static::creating(function ($user) {
            $user->activation_token = Str::random(10);
        });
    }
//在用户模型中，指明一个用户拥有多条微博
    public function statuses()
    {
        return $this->hasMany(Status::class);
    }  
/*在开始之前，我们需要在用户模型中定义一个 feed 方法，该方法将当前用户发布过的所有微博从数据库中取出，并根据创建时间来倒序排序*/
    public function feed()
    {
        return $this->statuses()
                    ->orderBy('created_at', 'desc');
    }

    public function followers()
    {
        return $this->belongsToMany(User::Class, 'followers', 'user_id', 'follower_id');
    }

    public function followings()
    {
        return $this->belongsToMany(User::Class, 'followers', 'follower_id', 'user_id');
    }

    public function follow($user_ids)
    {
        if ( ! is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->sync($user_ids, false);
    }

    public function unfollow($user_ids)
    {
        if ( ! is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->detach($user_ids);
    }

    public function isFollowing($user_id)
    {
        return $this->followings->contains($user_id);
    }
}
