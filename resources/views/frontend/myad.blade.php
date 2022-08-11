@extends('layouts.frontend.layout_one')

@section('title', __('my_ads'))

@section('content')
    <!-- breedcrumb section start  -->
    <x-frontend.breedcrumb-component :background="$cms->dashboard_my_ads_background">
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
                <a class="breedcrumb__page-link text--body-3">{{ __('my_ads') }}</a>
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

                    @include('layouts.frontend.partials.dashboard-myads-filter', [
                        'routeName' => 'frontend.adds',
                    ])

                    <div class="dashboard__myads">
                        <div class="header-table">
                            <div class="header-item">
                                <h6 class="text--body-4-600">
                                    {{ __('ads') }}
                                </h6>
                            </div>
                            <div class="header-item">
                                <h6 class="text--body-4-600">
                                    {{ __('date') }}
                                </h6>
                            </div>
                            <div class="header-item">
                                <h6 class="text--body-4-600">
                                    {{ __('prices') }}
                                </h6>
                            </div>
                            <div class="header-item">
                                <h6 class="text--body-4-600">
                                    {{ __('status') }}
                                </h6>
                            </div>
                            <div class="header-item">
                                <h6 class="text--body-4-600">
                                    {{ __('action') }}
                                </h6>
                            </div>
                        </div>
                        <div class="body">
                            @forelse ($ads as $ad)
                                <x-dashboard.ads-lg :ad="$ad">
                                    @if ($ad->status != 'declined')
                                        <a href="{{ route('frontend.post.edit', $ad->slug) }}" class="btn">
                                            <span class="icon--left">
                                                <x-svg.edit-icon />
                                            </span>
                                            {{ __('edit') }}
                                        </a>
                                    @endif
                                    <ul class="edit">
                                        <li class="edit-icon">
                                            <span class="icon-toggle">
                                                <x-svg.three-dots-icon />
                                            </span>

                                            <ul class="edit-dropdown">
                                                <li class="edit-dropdown__item">
                                                    <x-dashboard.view-ad :ad="$ad"></x-dashboard.view-ad>
                                                </li>
                                                <li class="edit-dropdown__item">
                                                    @if ($ad->status === 'sold')
                                                        <x-dashboard.make-active :ad="$ad" />
                                                    @else
                                                        <x-dashboard.make-expire :ad="$ad" />
                                                    @endif
                                                </li>
                                                <li class="edit-dropdown__item">
                                                    <x-dashboard.delete-ad :ad="$ad" />
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </x-dashboard.ads-lg>
                                <x-dashboard.ads-sm :ad="$ad">
                                    <ul class="edit" id="edit">
                                        <li class="edit-icon">
                                            <span class="icon-toggle">
                                                <x-svg.three-dots-icon />
                                            </span>

                                            <ul class="edit-dropdown">
                                                <li class="edit-dropdown__item">
                                                    <a href="{{ route('frontend.post.edit', $ad->slug) }}"
                                                        class="edit-dropdown__link">
                                                        <span class="icon">
                                                            <x-svg.edit-icon />
                                                        </span>
                                                        <h5 class="text--body-4">{{ __('edit') }}</h5>
                                                    </a>
                                                </li>
                                                <li class="edit-dropdown__item">
                                                    <x-dashboard.view-ad :ad="$ad"></x-dashboard.view-ad>
                                                </li>
                                                <li class="edit-dropdown__item">
                                                    @if ($ad->status === 'sold')
                                                        <x-dashboard.make-active :ad="$ad" />
                                                    @else
                                                        <x-dashboard.make-expire :ad="$ad" />
                                                    @endif
                                                </li>
                                                <li class="edit-dropdown__item">
                                                    <x-dashboard.delete-ad :ad="$ad" />
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </x-dashboard.ads-sm>
                            @empty
                                <x-not-found2 />
                            @endforelse
                        </div>
                    </div>
                    <div class="page-navigation">
                        {{ $ads->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- dashboard section end  -->

@endsection

@section('adlisting_style')
    <link rel="stylesheet" href="{{ asset('frontend/style.css') }}">
    <style>
        .card-edit__item {
            text-align: center;
        }


        .header-table {
            position: relative;
            min-height: 45px;
            -webkit-transition: all 0.4s ease-in-out;
            transition: all 0.4s ease-in-out;
        }

        .dashboard__myads .header-table {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            margin-top: 32px;
            background-color: #fff;
            -webkit-box-shadow: 0px -1px 0px 0px #ebeef7 inset;
            box-shadow: 0px -1px 0px 0px #ebeef7 inset;
        }
    </style>

@endsection

@section('frontend_script')
    <script src="{{ asset('frontend') }}/js/plugins/bvselect.js"></script>
    <script>
        var category = new BVSelect({
            selector: '#myadFilterCategory',
            searchbox: false,
            offset: false,
            placeholder: 'All category',
            search_autofocus: true,
            breakpoint: 450,
        });
        var sortBy = new BVSelect({
            selector: '#sortByFilter',
            searchbox: false,
            offset: false,
            placeholder: 'Sort By',
            search_autofocus: true,
            breakpoint: 450,
        });

        // Cards Edit menu
        var editBtn = document.querySelectorAll('#edit');
        if (editBtn) {
            editBtn.forEach((item) => {
                item.addEventListener('click', () => {
                    item.classList.toggle('active');
                });
            });
        }
    </script>
@endsection
