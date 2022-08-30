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
<div>
    <section class="breedcrumb" style="background: url('{{ asset($store->banner) }}') center center/cover no-repeat;">
        <div class="container">
            <h2 class="breedcrumb__title text--heading-2">{{ $store->name }}</h2>
            <ul class="breedcrumb__page">
                <li class="breedcrumb__page-item">
                    <a href="{{ route('frontend.index') }}" class="breedcrumb__page-link text--body-3">{{ __('home') }}</a>
                </li>
                <li class="breedcrumb__page-item">
                    <a class="breedcrumb__page-link text--body-3">/</a>
                </li>
                {{ $store->name }}
            </ul>
        </div>
    </section>
</div>


    <!-- breedcrumb section end  -->
    {{-- <x-frontend.adlist-search class="adlist-search" :categories="$categories" :dark="false" :total-ads="$adlistings->total()" /> --}}

    <section class="section ad-list">
        <div class="container">
            <div class="row">

                <div class="col-xl-9">
                    <div class="ad-list__content row">
                        @forelse ($userStore->ads as $ad)
                        <div class="col-lg-4 col-md-6">
                            <div class="cards cards--one {{ $ad->featured ? 'cards--highlight' : '' }}">
                                <a href="{{ route('frontend.addetails', $ad->slug) }}" class="cards__img-wrapper">
                                    <img src="{{ asset($ad->thumbnail) }}" onerror="this.src='{{ asset('uploads/img2.jpg')}}'"  alt="card-img" class="img-fluid" />
                                </a>
                                <div class="cards__info">
                                    <div class="cards__info-top">
                                        <h6 class="text--body-4 cards__category-title">
                                            <span class="icon">
                                                <i class="{{ $ad->category->icon }}" style="font-size: 15px"></i>
                                            </span>
                                            {{ $ad->category->name }}
                                        </h6>
                                        <a href="{{ route('frontend.addetails', $ad->slug) }}" class="text--body-3-600 cards__caption-title">
                                            {{ \Illuminate\Support\Str::limit($ad->title, 30, $end = '...') }}
                                        </a>
                                        @if (isset($adfields) && $adfields && count($adfields))
                                            <div class="d-flex flex-wrap justify-content-between mt-2">
                                                @foreach ($adfields as $adfield)
                                                    @if (isset($adfield->customField) && $adfield->customField && $adfield->value)
                                                        <div class="mr-1 text-left">
                                                            <i class="{{ $adfield->customField->icon }}"></i>
                                                            <small><strong>{{ $adfield->customField->name }}</strong></small>:
                                                            <small><span>
                                                                    {{ $adfield->customField->type == 'checkbox' ? $adfield->customField->values[0]->value : $adfield->value }}
                                                                </span></small>
                                                        </div>
                                                    @elseif (isset($adfield->custom_field) && $adfield->custom_field && $adfield->value)
                                                        <div class="mr-1 text-left">
                                                            <i class="{{ $adfield->custom_field->icon }}"></i>
                                                            <small><strong>{{ $adfield->custom_field->name }}</strong></small>:
                                                            <small><span>
                                                                    {{ $adfield->custom_field->type == 'checkbox' ? $adfield->custom_field->values[0]->value : $adfield->value }}
                                                                </span></small>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="cards__info-bottom">
                                        <h6 class="cards__location text--body-4">
                                            <span class="icon">
                                                <x-svg.location-icon width="20" height="20" stroke="#27C200" />
                                            </span>
                                            {{ Str::limit($ad->region, 10, '...') }} {{ $ad->region ? ', ' : '' }} {{ $ad->country }}
                                        </h6>
                                        <span class="cards__price-title text--body-3-600">
                                            {{ changeCurrency($ad->price) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @empty
                            <x-not-found2 message="{{ __('no_ads_found') }}" />
                        @endforelse
                    </div>
                    <div class="page-navigation">
                        {{ $userStore->paginate(1)->links() }}
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
