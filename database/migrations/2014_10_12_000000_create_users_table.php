<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//可以看到在该文件里面定义了一个 CreateUsersTable 类，并继承自 Migration 基类。CreateUsersTable 有两个方法 up 和 down ：

//当我们运行迁移时，up 方法会被调用；
//当我们回滚迁移时，down 方法会被调用
class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
//在 up 方法里面，我们通过调用 Schema 类的 create 方法来创建 users 表    
    public function up()
    {
//create 方法会接收两个参数：一个是数据表的名称，另一个则是接收 $table（Blueprint 实例）的闭包。 
//通过 Blueprint 的实例 $table 为 users 表创建所需的数据库字段       
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            //由 timestamps 方法创建了一个 created_at 和一个 updated_at 字段，分别用于保存用户的创建时间和更新时间。
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
