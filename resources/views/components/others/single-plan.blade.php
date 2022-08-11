<div class="col-xl-4 col-lg-6">
    <div class="plan-card {{ $plan->recommended ? 'plan-card--active' : '' }}">
        <div class="plan-card__top">
            <h2 class="plan-card__title text--body-1"> {{ $plan->label }} </h2>
            <p class="plan-card__description">
                {{ $plan->description }}
            </p>
            <div class="plan-card__price">
                <h5 class="text--display-3"> {{ changeCurrency($plan->price) }}</h5>
                @if ($setting->subscription_type == 'recurring')
                    @if ($plan->interval == 'custom_date')
                        <span class="text--body-3">/{{ $plan->custom_interval_days }} {{ __('days') }}</span>
                    @else
                        <span class="text--body-3">/{{ $plan->interval }}</span>
                    @endif
                @endif
            </div>
            @if (auth('user')->check())
                <a href="{{ route('frontend.priceplanDetails', $plan->label) }}"
                    class="plan-card__select-pack btn btn--bg w-100">
                    {{ __('choose_plan') }}
                    <span class="icon--right">
                        <x-svg.right-arrow-icon />
                    </span>
                </a>
            @else
                <a href="{{ route('users.login') }}" class="plan-card__select-pack btn btn--bg w-100 login_required">
                    {{ __('choose_plan') }}
                    <span class="icon--right">
                        <x-svg.right-arrow-icon />
                    </span>
                </a>
            @endif
        </div>
        <div class="plan-card__bottom">
            <div class="plan-card__package">
                <div class="plan-card__package-list active">
                    <span class="icon">
                        <x-svg.check-icon />
                    </span>
                    <h5 class="text--body-3">{{ __('post') }} {{ $plan->ad_limit }} {{ __('ads') }}</h5>
                </div>
                <div class="plan-card__package-list {{ $plan->featured_limit ? 'active' : '' }}">
                    <span class="icon">
                        <x-svg.check-icon />
                    </span>
                    <h5 class="text--body-3">{{ $plan->featured_limit }} {{ __('featured_ads') }}</h5>
                </div>
                <div class="plan-card__package-list {{ $plan->badge ? 'active' : '' }} ">
                    <span class="icon">
                        <x-svg.check-icon />
                    </span>
                    <h5 class="text--body-3">{{ __('special_membership_badge') }}</h5>
                </div>
            </div>
        </div>
    </div>
</div>
