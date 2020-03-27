<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{//fillable 属性，来指定在微博模型中可以进行正常更新的字段
	protected $fillable = ['content'];
	
    public function user()
    {//一条微博属于一个户
        return $this->belongsTo(User::class);
    }
}
