<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Mail;


class UsersController extends Controller
{
    public function create()
    {
        return view('users.create');
    }
/*
Laravel 会自动解析定义在控制器方法（变量名匹配路由片段）中的 Eloquent 模型类型声明
由于 show() 方法传参时声明了类型 —— Eloquent 模型 User，对应的变量名 $user 会匹配路由片段中的 {user}，这样，Laravel 会自动注入与请求 URI 中传入的 ID 对应的用户模型实例*/
    public function show(User $user)
    {
/*
我们将用户对象 $user 通过 compact 方法转化为一个关联数组，并作为第二个参数传递给 view 方法，将数据与视图进行绑定
*/        
        return view('users.show', compact('user'));
    }

//request （要求）用户输入的数据 用该参数来获得用户的所有输入数据
    public function store(Request $request)
	{
    $this->validate($request, [
        'name' => 'required|unique:users|max:50',
        'email' => 'required|email|unique:users|max:255',
        'password' => 'required|confirmed|min:6'
    ]);
/*用户模型 User::create() 创建成功后会返回一个用户对象，并包含新注册用户的所有信息。我们将新注册用户的所有信息赋值给变量 $user，并通过路由跳转来进行数据绑定*/
    $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            //'password' => bcrypt($request->password),
            'password' => $request->password,
        ]);

        $this->sendEmailConfirmationTo($user);
        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
        return redirect('/');
/*我们可以使用 session() 方法来访问会话实例。而当我们想存入一条缓存的数据，让它只在下一次的请求内有效时，则可以使用 flash 方法。flash 方法接收两个参数，第一个为会话的键，第二个为会话的值，我们可以通过下面这行代码的为会话赋值 */   
// 在 Laravel 中，如果要让一个已认证通过的用户实例进行登录，可以使用以下方法
        //Auth::login($user);
        $this->sendEmailConfirmationTo($user);
    	session()->flash('info', '由于 HTTP 协议是无状态的，所以 Laravel 提供了一种用于临时保存用户数据的方法 - 会话（Session），并附带支持多种会话后端驱动，可通过统一的 API 进行使用~');
        /*（重新调配）*/
        return redirect()->route('users.show', [$user]);
    
	}

    protected function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $from = 'summer@example.com';
        $name = 'Summer';
        $to = $user->email;
        $subject = "感谢注册 Weibo 应用！请确认你的邮箱。";

        Mail::send($view, $data, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });
    }



    public function edit(User $user)
    {//（批准）
         $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    public function update(User $user, Request $request)
    {
        $this->authorize('update', $user);
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'required|confirmed|min:6'
        ]);

        $user->update([
            'name' => $request->name,
            'password' => bcrypt($request->password),
        ]);
        session()->flash('success', '个人资料更新成功！');
        return redirect()->route('users.show', $user->id);
    }

    public function __construct()
        { //auth认证 （中间件）
        $this->middleware('auth', [            
            'except' => ['show', 'create', 'store','index','confirmEmail']
        ]);
        //guest客人
        //只让未登录用户访问注册页面：
        $this->middleware('guest', [
            'only' => ['create']
        ]);
        }  
    public function index()
        {
        //$users = User::all();
        $users = User::paginate(10);
        return view('users.index', compact('users'));
        }  

    public function destroy(User $user)
    {
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }

    
/*在 confirmEmail 中，我们会先根据路由传送过来的 activation_token 参数从数据库中查找相对应的用户，Eloquent 的 where 方法接收两个参数，第一个参数为要进行查找的字段名称，第二个参数为对应的值，查询结果返回的是一个数组，因此我们需要使用 firstOrFail 方法来取出第一个用户，在查询不到指定用户时将返回一个 404 响应。在查询到用户信息后，我们会将该用户的激活状态改为 true，激活令牌设置为空。最后将激活成功的用户进行登录，并在页面上显示消息提示和重定向到个人页面。*/
    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
        return redirect()->route('users.show', [$user]);
    }

}
