@component('mail::message')
# Change password request

Click the button to reset your password.

@component('mail::button', ['url' => 'http://localhost:4200/response-password-reset?token='.$token->token])
Reset Password
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
