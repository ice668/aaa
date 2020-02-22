<!-- 在我们对 errors 进行使用时，要先使用 count($errors) 检查其值是否为空 -->
@if (count($errors) > 0)
  <div class="alert alert-danger">
      <ul>
          @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
      </ul>
  </div>
@endif