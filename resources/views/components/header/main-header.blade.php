<header class="header header--home-three header--four">
    <div class="container navigation-bar">
        <div class="d-flex align-items-center ">
            <button class="toggle-icon  ">
                <span class="toggle-icon__bar"></span>
                <span class="toggle-icon__bar"></span>
                <span class="toggle-icon__bar"></span>
            </button>
            <!-- Brand Logo -->
            <a href="{{ route('frontend.index') }}" class="navigation-bar__logo">
                <img src="{{ $settings->logo_image_url }}" alt="brand-logo" class="logo-dark">
            </a>
        </div>
        <!-- Menu -->
        <ul class="menu">
            <li class="menu__item">
                <a href="{{ route('frontend.index') }}" class="menu__link">{{ __('home') }}</a>
            </li>
            <li class="menu__item">
                <a href="{{ route('frontend.adlist') }}" class="menu__link">{{ __('ads') }}</a>
            </li>
            @if ($blog_enable)
                <li class="menu__item">
                    <a href="{{ route('frontend.blog') }}" class="menu__link">{{ __('blog') }}</a>
                </li>
            @endif
            @if ($priceplan_enable)
                <li class="menu__item">
                    <a href="{{ route('frontend.priceplan') }}" class="menu__link">{{ __('pricing') }}</a>
                </li>
            @endif
        </ul>
        <!-- Action Buttons -->
        <div class="navigation-bar__buttons">
            @if (auth('user')->check())
                @php
                    $unread_messages = App\Models\Messenger::where('to_id', auth('user')->id())
                        ->where('body', '!=', '.')
                        ->where('read', 0)
                        ->count();
                @endphp
                <a href="{{ route('frontend.dashboard') }}" class="user position-relative">
                    <div class="user__img-wrapper ">
                        <img src="{{ auth('user')->user()->image_url }}"
                            style="width: 40px; height: 40px; border-radius: 50%" alt="User Image">

                        <span id="unread_count_custom4" class="text-danger {{ $unread_messages ? '' : 'd-none' }}"
                            amount="{{ $unread_messages }}">
                            {{ $unread_messages }}
                        </span>
                    </div>

                </a>
                <a href="{{ route('frontend.post') }}" class="btn">
                    <span class="icon--left">
                        <x-svg.image-select-icon />
                    </span>
                    {{ __('post_ads') }}
                </a>
            @else
                <a href="{{ route('users.login') }}" class="btn btn--bg">{{ __('sign_in') }}</a>
                <a href="{{ route('users.login') }}" class="btn login_required">
                    <span class="icon--left">
                        <x-svg.image-select-icon />
                    </span>
                    {{ __('post_ads') }}
                </a>
            @endif

            <x-frontend.language-switcher />
        </div>
        <!-- Responsive Navigation Menu  -->
        <x-frontend.responsive-menu />
    </div>
</header>
