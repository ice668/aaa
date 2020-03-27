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
//企图 资格
       if (Auth::attempt($credentials, $request->has('remember'))) {//Auth::user() 方法来获取 当前登录用户 的信息，并将数据传送给路由//激活
            if(Auth::user()->activated) {
               session()->flash('success', '欢迎回来！');
               $fallback = route('users.show', Auth::user());
               return redirect()->intended($fallback);
           } else {
                //uth::logout() 方法来实现用户的退出功能
               Auth::logout();
               session()->flash('warning', '你的账号未激活，请检查邮箱中的注册邮件进行激活。');
               return redirect('/');
                  }
            } else {//想存入下一条缓存的数据
           session()->flash('danger', '很抱歉，您的邮箱和密码不匹配');
           return redirect()->back()->withInput();
              }      
    }

    public function destroy()
    {//Laravel 默认提供的 Auth::logout() 方法来实现用户的退出功能
        Auth::logout();
        session()->flash('success', '您已成功退出！');
        return redirect('login');
    }

    public function __construct()
    {//只让未登录用户访问登录页面
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }


}
