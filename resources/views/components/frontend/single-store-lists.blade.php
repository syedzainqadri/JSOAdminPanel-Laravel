<div class="col-lg-4 col-md-6">
    <div class="cards cards--one">
        <a href="{{ route('frontend.store.ad-lists', $store->user_id) }}" class="cards__img-wrapper">
            @if ($store->banner)
                <img src="{{ asset($store->banner) }}" alt="card-img" class="img-fluid" />
            @else
                <img src="{{ asset('backend/image/default-ad.png') }}" alt="card-img" class="img-fluid" />
            @endif
        </a>
        <div class="cards__info">
            <div class="cards__info-top">
                <h6 class="text--body-4 cards__category-title">
                    {{ $store->name }}
                </h6>
                <a href="{{ route('frontend.store.ad-lists', $store->user_id) }}" class="text--body-3-600 cards__caption-title">
                    {{ \Illuminate\Support\Str::limit($store->title, 30, $end = '...') }}
                </a>
            </div>
        </div>
    </div>
</div>
