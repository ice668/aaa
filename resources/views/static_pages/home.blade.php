@extends('layouts.default')
@section('title', 'section主页')

@section('content')
  <div class="jumbotron">
  	<h1>whats 你愁啥？？？？？</h1>
  	<p class="lead">不要点这里 <a href="https://learnku.com/courses/laravel-essential-training">？？</a>可以嘛</p>

  	 <p>
      一切，都是浮云。
    </p>

    <p>
      <a class="btn btn-lg btn-success" href="{{ route('signup') }}" role="button">注册个jiji？</a>
    </p>
  </div>
@stop
