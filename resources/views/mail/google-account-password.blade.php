@component('mail::message')
# Hello {{ $user->name }}!

Your account was created using your Google sign-in. To log in directly on the platform, use the temporary password below:

@component('mail::panel')
**{{ $plainPassword }}**
@endcomponent

Please change it from your profile as soon as you sign in.

@component('mail::button', ['url' => route('dashboard')])
Go to dashboard
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

