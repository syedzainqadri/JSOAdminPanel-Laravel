<div class="col-lg-6 order-1 order-lg-0">
    <div class="registration-form">
        <h2 class="text-center text--heading-1 registration-form__title">{{ __('sign_up') }}</h2>
        {{-- Social Login --}}
        <x-auth.social-login />

        <div class="registration-form__wrapper">
            <form action="{{ route('customer.register') }}" method="POST">
                @csrf
                <div class="input-field">
                    <input value="{{ old('name') }}" type="text" placeholder="{{ __('full_name') }}" name="name"
                        class="@error('name') is-invalid border-danger @enderror" />
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="input-field">
                    <input value="{{ old('username') }}" type="text" placeholder="{{ __('username') }}"
                        name="username" class="@error('username') is-invalid border-danger @enderror" />
                    @error('username')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="input-field">
                    <input value="{{ old('email', request('email')) }}" type="email"
                        placeholder="{{ __('email_address') }}" name="email"
                        class="@error('email') is-invalid border-danger @enderror" />
                    @error('email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="input-field">
                    <input type="password" name="password" placeholder="{{ __('password') }}" id="password"
                        class="@error('password') is-invalid border-danger @enderror" />
                    <span class="icon icon--eye {{ $errors->has('password') ? 'height-45' : '' }}"
                        onclick="showPassword('password',this)">
                        <x-svg.eye-icon />
                    </span>
                    @error('password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="input-field">
                    <input type="password" name="password_confirmation" placeholder="{{ __('confirm_password') }}"
                        id="cpassword" class="@error('password_confirmation') is-invalid border-danger @enderror" />
                    <span class="icon icon--eye {{ $errors->has('password_confirmation') ? 'height-45' : '' }}"
                        onclick="showPassword('cpassword',this)">
                        <x-svg.eye-icon />
                    </span>
                    @error('password_confirmation')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="registration-form__option">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="checkme" />
                        <label class="form-check-label" for="checkme">
                            {{ __('read_agree') }} <a
                                href="{{ route('frontend.privacy') }}">{{ __('privacy_policy') }}</a>
                            {{ __('and') }}
                            <a href="{{ route('frontend.terms') }}">
                                {{ __('terms_conditions') }}
                            </a>
                        </label>
                    </div>
                </div>
                <button class="btn btn--lg w-100 registration-form__btns" type="submit">
                    {{ __('sign_up') }}
                    <span class="icon--right">
                        <x-svg.right-arrow-icon stroke="#fff" />
                    </span>
                </button>
                <p class="text--body-3 registration-form__redirect">{{ __('have_account') }} ? <a
                        href="{{ route('users.login') }}">{{ __('sign_in') }}</a></p>
            </form>
        </div>
    </div>
</div>

@push('component_style')
    <style>
        .height-45 {
            height: 45px !important;
        }
    </style>
@endpush
