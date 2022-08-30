@extends('layouts.frontend.layout_one')

@section('title', __('account_setting'))

@section('content')
    <!-- breedcrumb section start  -->
    <x-frontend.breedcrumb-component :background="$cms->dashboard_account_setting_background">
        {{ __('overview') }}
        <x-slot name="items">
            <li class="breedcrumb__page-item">
                <a href="{{ route('frontend.dashboard') }}"
                    class="breedcrumb__page-link text--body-3">{{ __('dashboard') }}</a>
            </li>
            <li class="breedcrumb__page-item">
                <a class="breedcrumb__page-link text--body-3">/</a>
            </li>
            <li class="breedcrumb__page-item">
                <a class="breedcrumb__page-link text--body-3">{{ __('settings') }}</a>
            </li>
        </x-slot>
    </x-frontend.breedcrumb-component>
    <!-- breedcrumb section end  -->

    <!-- dashboard section start  -->
    <section class="section dashboard">
        <div class="container">
            @include('frontend.dashboard-alert')
            <div class="row">
                <div class="col-xl-3">
                    @include('layouts.frontend.partials.dashboard-sidebar')
                </div>
                <div class="col-xl-9">
                    <div class="dashboard-setting">

                        <!-- Account Information -->
                        <div class="dashboard-setting__box account-information">
                            <h2 class="text--body-2-600">My Store</h2>
                            <form action="{{ route('frontend.updateStore') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="user-info">
                                    <div class="img">
                                        <div class="img-wrapper">
                                        <img src="{{ !empty($store) ? asset($store->banner) : ''}}" alt="user-img" onerror="this.src='{{ asset('uploads/img2.jpg')}}'" id="banner">
                                        </div>
                                        <input name="banner"
                                            onchange="document.getElementById('banner').src = window.URL.createObjectURL(this.files[0])"
                                            id="hiddenbannerInput" type="file" hidden
                                            accept="image/png, image/jpg, image/jpeg" class="@error('logo') is-invalid border-danger @enderror">
                                            @error('banner')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        <button onclick="$('#hiddenbannerInput').click()" class="btn btn--bg"
                                            type="button">{{ __('choose_banner') }}</button>
                                    </div>
                                </div>
                                <div class="user-info">
                                    <div class="img">
                                        <div class="img-wrapper">
                                            <img src="{{ !empty($store) ? asset($store->logo) : '' }}" alt="user-img" onerror="this.src='{{ asset('uploads/img2.jpg')}}'" id="logo">
                                        </div>
                                        <input name="logo"
                                            onchange="document.getElementById('logo').src = window.URL.createObjectURL(this.files[0])"
                                            id="hiddenlogoInput" type="file" hidden
                                            accept="image/png, image/jpg, image/jpeg" class="@error('logo') is-invalid border-danger @enderror">
                                            @error('logo')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        <button onclick="$('#hiddenlogoInput').click()" class="btn btn--bg"
                                            type="button">{{ __('choose_logo') }}</button>
                                    </div>
                                </div>
                                <div class="input-field__group">
                                    <div class="input-field">
                                        <x-forms.label name="name" for="Fname" />
                                        <input name="name" value="{{ !empty($store) ? $store->name : ''  }}" type="text"
                                            placeholder="Store name" id="Fname"
                                            class="@error('name') is-invalid border-danger @enderror">
                                        @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="input-field__group">
                                    <div class="input-field">
                                        <x-forms.label name="address " for="address" />
                                        <input name="address" value="{{ !empty($store) ? $store->address : ''  }}" type="text"
                                            placeholder="address" id="address"
                                            class="@error('address') is-invalid border-danger @enderror">
                                        @error('address')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <button class="btn" type="submit">{{ __('update_store') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- dashboard section end  -->
@endsection

@section('frontend_style')
    <style>
        .input-field .icon {
            top: 50% !important;
        }
    </style>
@endsection

@section('frontend_script')
    <script src="{{ asset('frontend') }}/js/plugins/passwordType.js"></script>
    <script type="text/javascript">
        // feature field
        function add_features_field() {
            $("#multiple_feature_part").append(`
            <div class="input-field__group">
                <div class="input-field">
                    <x-forms.label name="social_media" for="feature" />
                    <div>
                        <div class="input-field">
                            <select name="social_media[]"
                                class="form-control select-bg @error('social_media') border-danger @enderror">
                                <option value="" class="d-none">{{ __('select_one') }}</option>
                                <option value="facebook">Facebook</option>
                                <option value="twitter">Twitter</option>
                                <option value="instagram">Instagram</option>
                                <option value="youtube">Youtube</option>
                                <option value="linkedin">Linkedin</option>
                                <option value="pinterest">Pinterest</option>
                                <option value="reddit">Reddit</option>
                                <option value="github">Github</option>
                                <option value="website">Website</option>
                                <option value="other">Other</option>
                            </select>
                            @error('social_media')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="input-field">
                    <x-forms.label name="url" for="feature" />
                    <div id="multiple_feature_part">
                        <div class="input-field">
                            <input name="url[]" type="url"
                                placeholder="{{ __('url') }}" id="adname"
                                class="@error('title') border-danger @enderror" />
                        </div>
                    </div>
                </div>
                <a role="button" id="remove_item"
                        class="btn bg-danger text-light"><i class="fas fa-times"></i></a>
            </div>
        `);
        }

        $(document).on("click", "#remove_item", function() {
            $(this).parent().parent('div').remove();
        });
    </script>
@endsection
