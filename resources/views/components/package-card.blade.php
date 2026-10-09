@props(['package'])
<article class="package-card">
    <div class="package-card-top"><span class="eyebrow">{{ str_pad((string) $package->display_order, 2, '0', STR_PAD_LEFT) }} / Membership</span><span class="tier-dot tier-{{ $package->slug }}"></span></div>
    <h3>{{ $package->name }}</h3>
    <p>{{ $package->description }}</p>
    @if($package->price !== null)<p class="package-price">KES {{ number_format((float) $package->price, 0) }} <span>/ {{ $package->renewal_months }} months</span></p>@endif
    <ul>@foreach(($package->benefits ?? []) as $benefit)<li>{{ $benefit }}</li>@endforeach</ul>
    <a class="text-link" href="{{ route('membership.package', $package) }}">Explore {{ $package->name }} <span aria-hidden="true">→</span></a>
</article>
