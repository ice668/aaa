<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Auth;
//Authenticatable(认证) 是授权相关功能的引用。
class User extends Authenticatable
{
//Notifiable（依法须上报的） 是消息通知相关功能引用。
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
//（可填充）fillable 在过滤用户提交的字段，只有包含在该属性中的字段才能够被正常更新
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
//隐藏
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
//一个用户拥有多条微博得到许多
    public function statuses()
    {
        return $this->hasMany(Status::class);
    }  
/*在开始之前，我们需要在用户模型中定义一个 feed 方法，该方法将当前用户发布过的所有微博从数据库中取出，并根据创建时间来倒序排序*/
    public function feed()
    {
         /*return $this->statuses()
                    ->orderBy('created_at', 'desc');*/
 //通过 followings 方法取出所有关注用户的信息 再借助 pluck 方法将 id 进行分离并赋值给 user_ids                  
        $user_ids = $this->followings->pluck('id')->toArray();
        array_push($user_ids, $this->id);
        return Status::whereIn('user_id', $user_ids)
                              ->with('user')
                              ->orderBy('created_at', 'desc');
    }
//信徒 归属于许多 user_id 是定义在关联中的模型外键名，而第四个参数 follower_id 则是要合并的模型外键名
    public function followers()
    {
        return $this->belongsToMany(User::Class, 'followers', 'user_id', 'follower_id');
    }
//adj信奉着 追随 喜爱 支持
    public function followings()
    {
        return $this->belongsToMany(User::Class, 'followers', 'follower_id', 'user_id');
    }
//vt跟随
    public function follow($user_ids)
    {//is_array 用于判断参数是否为数组
        if ( ! is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        //同时发生sync
        $this->followings()->sync($user_ids, false);
    }
//取消关注
    public function unfollow($user_ids)
    {
        if ( ! is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->detach($user_ids);
    }
//依附 用户 B 是否包含在用户 A 的关注人列表上
    public function isFollowing($user_id)
    {//追随 含有
        return $this->followings->contains($user_id);
    }
}
