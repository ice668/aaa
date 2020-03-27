<a href="{{ route('users.show', $user->id) }}">
  <img src="{{ $user->gravatar('150') }}" alt="{{ $user->name }}" class="gravatar"/>
</a>
<h4>{{ $user->name }}</h4>