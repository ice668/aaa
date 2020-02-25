<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
class SessionsController extends Controller
{
     public function create()
    {
        return view('sessions.create');
    }

//我们可以使用 Illuminate\Http\Request 实例来接收用户的所有输入数据，当我们需要取出 Request 实例的单个值时，可以使用以下方法
    public function store(Request $request)
    {//资格验证
       $credentials = $this->validate($request, [
           'email' => 'required|email|max:255',
           'password' => 'required'
       ]);

        if (Auth::attempt($credentials)) {
        	 session()->flash('success', '欢迎回来！');
        	 return redirect()->route('users.show', [Auth::user()]);
           // 重新调配Auth::user() 方法来获取 当前登录用户 的信息，并将数据传送给路由
       } else {
       		 session()->flash('danger', '很抱歉，您的邮箱和密码不匹配');
           // 登录失败后的相关操作
       		 return redirect()->back()->withInput();
       	//使用 withInput() 后模板里 old('email') 将能获取到上一次用户提交的内容，这样用户就无需再次输入邮箱等内容
       }
       return;
    }

    public function destroy()
    {
        Auth::logout();
        session()->flash('success', '您已成功退出！');
        return redirect('login');
    }


}
