@extends('layouts.frontend.layout_one')

@section('title', __('ads'))

@section('meta')
    @php
    $data = metaData('ads');
    @endphp

    <meta name="title" content="{{ $data->title }}">
    <meta name="description" content="{{ $data->description }}">

    <meta property="og:image" content="{{ $data->image_url }}" />
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:title" content="{{ $data->title }}">
    <meta property="og:url" content="{{ route('frontend.adlist') }}">
    <meta property="og:type" content="article">
    <meta property="og:description" content="{{ $data->description }}">

    <meta name=twitter:card content={{ $data->image_url }} />
    <meta name=twitter:site content="{{ config('app.name') }}" />
    <meta name=twitter:url content="{{ route('frontend.adlist') }}" />
    <meta name=twitter:title content="{{ $data->title }}" />
    <meta name=twitter:description content="{{ $data->description }}" />
    <meta name=twitter:image content="{{ $data->image_url }}" />
@endsection

@section('content')
    <x-frontend.breedcrumb-component :background="$cms->ads_background">
        {{ __('store') }}
        <x-slot name="items">
            <li class="breedcrumb__page-item">
                <a class="breedcrumb__page-link text--body-3">{{ __('store') }}</a>
            </li>
        </x-slot>
    </x-frontend.breedcrumb-component>
    <!-- breedcrumb section end  -->
    {{-- <x-frontend.adlist-search class="adlist-search" :categories="$categories" :dark="false" :total-ads="$adlistings->total()" /> --}}

    <section class="section ad-list">
        <div class="container">
            <div class="row">

                <div class="col-xl-9">
                    <div class="ad-list__content row">
                        @forelse ($stores as $store)
                          @include('components.frontend.single-store-lists', ['store' => $store])
                        @empty
                            <x-not-found2 message="{{ __('no_stores_found') }}" />
                        @endforelse
                    </div>
                    <div class="page-navigation">
                        {{ $stores->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
    </form>
@endsection

@section('adlisting_style')
    <link rel="stylesheet" href="{{ asset('frontend/css') }}/select2.min.css" />
    <link rel="stylesheet" href="{{ asset('frontend') }}/css/nouislider.min.css">
    <link rel="stylesheet" href="{{ asset('frontend/css') }}/select2-bootstrap-5-theme.css" />
@endsection

@section('frontend_script')
    <script src="{{ asset('frontend') }}/js/plugins/select2/js/select2.min.js"></script>
    <script src="{{ asset('frontend') }}/js/plugins/bvselect.js"></script>
    <script src="{{ asset('frontend') }}/js/plugins/nouislider.min.js"></script>
    <script src="{{ asset('frontend') }}/js/plugins/wNumb.min.js"></script>
    <script>
    </script>
@endsection
