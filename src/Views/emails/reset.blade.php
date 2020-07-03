@extends($layout)

@section('content')
    <p>{{ __('ambersive-api::forgotpassword.hello', ['username' => $user->username]) }}</p>
    <p>{{ __('ambersive-api::forgotpassword.linktext', ['code' => $code]) }}</p>
@stop


