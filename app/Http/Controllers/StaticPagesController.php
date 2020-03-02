<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Status;
use Auth;

class StaticPagesController extends Controller
{
    public function home()
    {//使用 Auth::check() 来检查用户是否已登录。另外我们还对微博做了分页处理的操作，每页只显示 30 条微博
        $feed_items = [];
        if (Auth::check()) {
            $feed_items = Auth::user()->feed()->paginate(30);}
        return view('static_pages/home', compact('feed_items'));
    }

    public function help()
    {
        return view('static_pages.help');
    }

    public function about()
    {
        return view('static_pages.about');
    }
}