@extends('layouts.public')
@section('title', 'Nomination response')
@section('content')
<section class="page-hero"><p class="eyebrow">Premium Business Den / Member reference</p><h1>Your response<br><em>has been recorded.</em></h1><p>Thank you for {{ $response === 'accepted' ? 'supporting this nomination' : 'letting us know' }}. The membership team will take it from here.</p><a class="button" href="{{ route('home') }}">Return to the Den <span aria-hidden="true">→</span></a></section>
@endsection
