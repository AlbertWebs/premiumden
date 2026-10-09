@extends('layouts.member')
@section('title', 'Member settings')
@section('content')
<section class="profile-edit-shell">@if(session('status'))<div class="profile-success" role="status">{{ session('status') }}</div>@endif<form method="POST" action="{{ route('member.settings.update') }}">@csrf @method('PUT')<label class="profile-list-toggle"><input type="checkbox" name="email_message_notifications" value="1" @checked(old('email_message_notifications', auth()->user()->email_message_notifications))><span>Email me when I receive a new member message.</span></label><label class="profile-list-toggle"><input type="checkbox" name="email_society_updates" value="1" @checked(old('email_society_updates', auth()->user()->email_society_updates))><span>Email me about important society announcements.</span></label><button class="button" type="submit">Save preferences <span aria-hidden="true">→</span></button></form></section>
<section class="profile-edit-heading"><p class="eyebrow">Member space / Settings</p><h1>Your <em>preferences.</em></h1><p>Choose which optional email alerts you receive. Essential application, payment and account access messages remain enabled.</p></section>
@endsection
