<header class="header header--two" id="sticky-menu">
    <div class="navigation-bar__top">
        <div class="container">
            <div class="navigation-bar">
                <div class="d-flex align-items-center">
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
                <!-- Search Field -->
                <form action="{{ route('frontend.adlist.search') }}" method="GET">
                    <div class="navigation-bar__search-field">
                        <input type="text" placeholder="{{ __('ads_title_keyword') }}..." name="keyword" />
                        <button type="submit" class="navigation-bar__search-icon">
                            <x-svg.search-icon />
                        </button>
                    </div>
                </form>
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
                            <div class="user__img-wrapper">
                                <img src="{{ auth('user')->user()->image_url }}"
                                    style="width: 40px; height: 40px; border-radius: 50%" alt="User Image">
                            </div>

                            <span id="unread_count_custom3"
                                class="text-danger unread-message-img {{ $unread_messages ? '' : 'd-none' }}"
                                amount="{{ $unread_messages }}">
                                {{ $unread_messages }}
                            </span>
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
                </div>
                <!-- Responsive Navigation Menu  -->
                <x-frontend.responsive-menu />
            </div>
        </div>
    </div>
    <div class="navigation-bar__bottom-wrap">
        <div class="container navigation-bar__bottom justify-content-between">
            <div class="d-flex align-items-center">
                <!-- category menu -->
                <ul class="category-menu">
                    <li class="category-menu__item">
                        <a href="#" class="category-menu__link">
                            {{ __('all_categories') }}
                            <span class="icon">
                                <x-svg.category-arrow-icon />
                            </span>
                        </a>
                        <ul class="category-menu__dropdown">
                            @foreach ($categories as $category)
                                {{-- Filter Form-2 --}}
                                <form method="GET" action="{{ route('frontend.adlist.search') }}"
                                    id="adFilterForm2" class="d-none">
                                    <input type="hidden" name="category" value="" id="adFilterInput2">
                                </form>

                                <li class="category-menu__dropdown__item">
                                    <a href="javascript:void(0)"
                                        onclick="adFilterFunctionTwo('{{ $category->slug }}')"
                                        class="category-menu__dropdown__link">
                                        <i class="category-icon {{ $category->icon }}" style="color: #66CCFF"></i>
                                        {{ $category->name }}
                                        @if ($category->subcategories->count() > 0)
                                            <span class="drop-icon">
                                                <x-svg.category-right-icon />
                                            </span>
                                        @endif
                                    </a>
                                    @if ($category->subcategories->count() > 0)
                                        <ul class="category-menu__subdropdown">
                                            @foreach ($category->subcategories as $subcategory)
                                                {{-- Filter Form-3 --}}
                                                <form method="GET" action="{{ route('frontend.adlist.search') }}"
                                                    id="adFilterForm3" class="d-none">
                                                    <input type="hidden" name="subcategory[]" value=""
                                                        id="adFilterInput3">
                                                </form>

                                                <li class="category-menu__subdropdown__item">
                                                    <a href="javascript:void(0)"
                                                        onclick="adFilterFunctionThree('{{ $subcategory->slug }}')"
                                                        class="category-menu__subdropdown__link">
                                                        {{ $subcategory->name }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </li>
                </ul>
                <!-- Category Item -->
                <ul class="categories">
                    @foreach ($top_categories as $category)
                        <li class="categories__item">
                            <a href="javascript:void(0)" onclick="adFilterFunctionTwo('{{ $category->slug }}')"
                                class="categories__link {{ request()->routeIs('frontend.index') ? 'active' : '' }} ">
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <x-frontend.language-switcher />

        </div>
    </div>
</header>
