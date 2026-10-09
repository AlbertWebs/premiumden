@extends('layouts.public')
@section('title', 'Leadership')
@section('content')
@php
    $directors = [
        ['name' => 'Kwek Leng Beng', 'role' => 'Chairman and Patron', 'image' => 'kwek-leng-beng', 'bio' => 'The supplied profile identifies him as Executive Chairman of Hong Leong Group Singapore.'],
        ['name' => 'Prof. Han Ke', 'role' => 'Board member', 'image' => 'han-ke', 'bio' => 'Professor in the School of Transportation and Logistics at Southwest Jiaotong University; previously a Senior Lecturer at Imperial College London.'],
        ['name' => 'Dr. Serge Allouche, MD', 'role' => 'Board member', 'image' => 'serge-allouche', 'bio' => 'A family medicine practitioner from France, with postgraduate study in tropical medicine and medical economics.'],
        ['name' => 'Abdalla Hamdok', 'role' => 'Board member', 'image' => 'abdalla-hamdok', 'bio' => 'Public administrator and former Prime Minister of Sudan. The profile notes his earlier service as Deputy Executive Secretary of the United Nations Economic Commission for Africa.'],
        ['name' => 'Phillip Green', 'role' => 'Board member', 'image' => 'phillip-green', 'bio' => 'The profile describes his work as a British retail entrepreneur and former chairman of Arcadia Group.'],
        ['name' => 'Dr. Silpah Owich', 'role' => 'Advisory member', 'image' => 'silpah-owich', 'bio' => 'The profile notes her leadership work in personal markets at Stanbic Bank Kenya.'],
        ['name' => 'Dr. Benny Otim', 'role' => 'Board member', 'image' => 'benny-otim', 'bio' => 'A former senior United Nations official with experience in diplomacy, conflict resolution and development.'],
        ['name' => 'Prof. Julia Ojiambo', 'role' => 'Board member', 'image' => 'julia-ojiambo', 'bio' => 'Kenyan educator and women’s rights advocate, noted for her public service and academic leadership.'],
        ['name' => 'Niyi Oluntoriba', 'role' => 'Board member', 'image' => 'niyi-oluntoriba', 'bio' => 'Investment analyst with experience across publishing, energy and financial services, including advisory and capital raising.'],
    ];
    $committee = [
        ['name' => 'Dr. Nelson M. Sechere', 'role' => 'Chairman', 'image' => 'nelson-sechere', 'bio' => 'The profile notes a PhD and MSc in strategic management and work in fundraising and community support.'],
        ['name' => 'Ms. Rina Mezradues Krinn', 'role' => 'Gold Membership Manager', 'image' => 'rina-mezradues-krinn', 'bio' => 'The profile describes experience in mineral trading, business development and customer service.'],
        ['name' => 'Mr. Alexander Tufail', 'role' => 'East Asia', 'image' => 'alexander-tufail', 'bio' => 'The profile describes business experience in construction, financing and acquisitions.'],
    ];
@endphp
<section class="page-hero leadership-hero"><div class="leadership-hero-copy"><p class="eyebrow">Premium Business Den / Leadership</p><h1>Leadership<br><em>at the Den.</em></h1><p>Board and management profiles recorded in the Den’s 2020–21 presentation.</p></div><div class="leadership-hero-mark" aria-hidden="true"><span>PB</span><i></i><small>Leadership archive</small></div></section>
<section class="leadership-archive-note"><div class="archive-period"><span>Presentation</span><strong>2020 <i>—</i> 2021</strong></div><div><strong>Historical record</strong><p>These names, roles and biographies reflect the supplied presentation and do not confirm current appointments.</p></div></section>
<section class="leadership-section"><div class="section-heading"><div><p class="eyebrow">01 / Board of directors</p><h2>Experience<br>around the <em>table.</em></h2></div><p>Profiles as presented in the board document.</p></div><div class="leadership-grid">@foreach($directors as $person)<article class="leadership-card"><span class="leadership-number" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><img src="{{ asset('images/board/'.$person['image'].'.webp') }}" alt="Portrait of {{ $person['name'] }}" loading="lazy"><div class="leadership-card-copy"><p class="eyebrow">{{ $person['role'] }}</p><h3>{{ $person['name'] }}</h3><p>{{ $person['bio'] }}</p></div></article>@endforeach</div></section>
<section class="leadership-section leadership-committee"><div class="section-heading"><div><p class="eyebrow">02 / Management committee</p><h2>People responsible<br>for <em>the work.</em></h2></div><p>Management roles and biographies from the same presentation.</p></div><div class="leadership-grid leadership-grid-committee">@foreach($committee as $person)<article class="leadership-card"><span class="leadership-number" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><img src="{{ asset('images/board/'.$person['image'].'.webp') }}" alt="Portrait of {{ $person['name'] }}" loading="lazy"><div class="leadership-card-copy"><p class="eyebrow">{{ $person['role'] }}</p><h3>{{ $person['name'] }}</h3><p>{{ $person['bio'] }}</p></div></article>@endforeach</div></section>
<section class="closing-cta"><p class="eyebrow eyebrow-light">Membership</p><h2>Meet the wider<br><em>community.</em></h2><a class="button button-gold" href="{{ route('application.intro') }}">Apply for membership <span aria-hidden="true">↗</span></a></section>
@endsection
