@component('mail::message')
# Hello {{ $user->name }},

We wanted to let you know that your profile information was recently updated on {{ config('app.name') }}.

Below is a summary of the changes made:

<ul style="padding-left:16px;">
@foreach($changes as $field => $change)
    <li>
        <strong>{{ ucfirst(str_replace('_', ' ', $field)) }}:</strong>
        <span style="color:#6366f1;">(old) {{ $change['old'] }}</span>
        <span style="color:#6b7280;">→</span>
        <span style="color:#059669;">(new) {{ $change['new'] }}</span>
    </li>
@endforeach
</ul>

@if(isset($changes['email']))
**Important:** Because your email address was changed, you will need to verify your new email the next time you log in.

@component('mail::button', ['url' => url('/email/verify')])
Verify Email
@endcomponent
@endif

If you did not make this change, please contact our support team immediately.

Thanks,<br>
{{ config('app.name') }}
@endcomponent 