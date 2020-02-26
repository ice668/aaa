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
// 前面我们介绍过的 Auth::attempt() 方法可接收两个参数，第一个参数为需要进行用户身份认证的数组，第二个参数为是否为用户开启『记住我』功能的布尔值。接下来让我们修改会话控制器中的 store 方法，为 Auth::attempt() 添加『记住我』参数。
        if (Auth::attempt($credentials, $request->has('remember'))) {
        	 session()->flash('success', '欢迎回来！');
        	 return redirect()->route('users.show', [Auth::user()]);
          /* redirect() 实例提供了一个 intended 方法，该方法可将页面重定向到上一次请求尝试访问的页面上，并接收一个默认跳转地址参数，当上一次请求记录为空时，跳转到默认地址上。*/
           return redirect()->intended($fallback);
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

    public function __construct()
    {
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }


}
