<div class="product-item__sidebar-item product-price">
    <h2 class="text--heading-2">{{ changeCurrency($price) }}</h2>
    <form action="{{ route('frontend.add.wishlist') }}" method="POST">
        @csrf

        @if (auth('user')->check())
            <input type="hidden" name="ad_id" value="{{ $id }}">
            <input type="hidden" name="user_id" value="{{ auth('user')->user()->id }}">
            <button class="btn--fav" type="submit">
                @if (isWishlisted($id))
                    <x-svg.heart-icon fill="#00AAFF" stroke="#00AAFF" stroke-width="0.5" />
                @else
                    <x-svg.heart-icon />
                @endif
            </button>
        @else
            <a href="{{ route('users.login') }}" class="btn--fav login_required" type="button">
                <x-svg.heart-icon />
            </a>
        @endif
    </form>
</div>
