@extends($layout)

@section('content')
    <p>{{ __('ambersive-api::register.mails.activation.hello', ['username' => $user->username]) }}</p>
    <p>{{ __('ambersive-api::register.mails.activation.linktext', ['code' => $code]) }} <a href="{{$url}}" target="_blank">{{ __('ambersive-api::register.mails.activation.clickhere') }}</a></p>
@stop


