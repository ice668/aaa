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
Laravel 将会自动查找 ID 为 1 的用户并赋值到变量 $user 中，如果数据库中找不到对应的模型实例，会自动生成 HTTP 404 响应*/
    public function show(User $user)
    {
/*
我们将用户对象 $user 通过 compact 方法转化为一个关联数组，并作为第二个参数传递给 view 方法，将数据与视图进行绑定
*/        

//由于我们之前进行了模型关联，因此取出一个用户的所有微博可以通过以下方式：
      //Eloquent 模型提供的 orderBy 方法，通过指定字段名和排序方式来对微博进行倒序排序。分页每页10条微博  
        $statuses = $user->statuses()
                           ->orderBy('created_at', 'desc')
                           ->paginate(10);
        return view('users.show', compact('user', 'statuses'));
    }

//request （要求）store 方法接受一个 Illuminate\Http\Request 实例参数，我们可以使用该参数来获得用户的所有输入数据
    public function store(Request $request)
	{//（数据验证）
    $this->validate($request, [
        'name' => 'required|unique:users|max:50',
        'email' => 'required|email|unique:users|max:255',
        'password' => 'required|confirmed|min:6'
    ]);
/*用户模型 User::create() 创建成功后会返回一个用户对象，并包含新注册用户的所有信息。我们将新注册用户的所有信息赋值给变量 $user，并通过路由跳转来进行数据绑定*/
    $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            //'password' => $request->password,
        ]);

        $this->sendEmailConfirmationTo($user);
        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
        return redirect('/');
/*我们可以使用 session() 方法来访问会话实例。而当我们想存入一条缓存的数据，让它只在下一次的请求内有效时，则可以使用 flash 方法。flash 方法接收两个参数，第一个为会话的键，第二个为会话的值，我们可以通过下面这行代码的为会话赋值 */   
// 在 Laravel 中，如果要让一个已认证通过的用户实例进行登录，可以使用以下方法
        //Auth::login($user);
        $this->sendEmailConfirmationTo($user);
    	session()->flash('info', '欢迎，您将在这里开启一段新的旅程~');
        /*（重新调配）*/
        return redirect()->route('users.show', [$user]);
    
	}

//该方法将用于发送邮件给指定用户。我们会在用户注册成功之后调用该方法来发送激活邮件
    protected function sendEmailConfirmationTo($user)
    {   //第一个参数是包含邮件消息的视图名称。
        $view = 'emails.confirm';
        //第二个参数是要传递给该视图的数据数组。
        $data = compact('user');
        $from = 'summer@example.com';
        $name = 'Summer';
        $to = $user->email;
        $subject = "感谢注册 Weibo 应用！请确认你的邮箱。";
        //最后是一个用来接收邮件消息实例的闭包回调，我们可以在该回调中自定义邮件消息的发送者、接收者、邮件主题等信息。
        Mail::send($view, $data, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });
    }



    public function edit(User $user)
    {//（批准）
         $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }
//update 动作来处理用户提交的个人信息。
    public function update(User $user, Request $request)
    {
        $this->authorize('update', $user);
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);

        

        $user->update([
            'name' => $request->name,
            'password' => bcrypt($request->password),
        ]);
        session()->flash('success', '个人资料更新成功！');
        return redirect()->route('users.show', $user->id);
    }

    public function __construct()
        { //auth认证 （中间件）用户个人信息、创建用户页面、创建用户
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
        //将该用户的激活状态改为 true
        $user->activated = true;
        //激活令牌设置为空
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
        return redirect()->route('users.show', [$user]);
    }

    public function followings(User $user)
    {
        $users = $user->followings()->paginate(30);
        $title = $user->name . '关注的人';
        return view('users.show_follow', compact('users', 'title'));
    }

    public function followers(User $user)
    {
        $users = $user->followers()->paginate(30);
        $title = $user->name . '的粉丝';
        return view('users.show_follow', compact('users', 'title'));
    }


}
