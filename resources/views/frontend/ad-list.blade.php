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
        {{ __('ad_list') }}
        <x-slot name="items">
            <li class="breedcrumb__page-item">
                <a class="breedcrumb__page-link text--body-3">{{ __('ad_list') }}</a>
            </li>
        </x-slot>
    </x-frontend.breedcrumb-component>
    <!-- breedcrumb section end  -->
    <x-frontend.adlist-search class="adlist-search" :categories="$categories" :dark="false" :total-ads="$adlistings->total()" />

    <section class="section ad-list">
        <div class="container">
            <div class="row">
                <div class="col-xl-3">
                    <div class="list-sidebar">
                        <div class="product-filter">
                            <h3>{{ __('product_filters') }}</h3>
                            <span class="close">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15.625 4.375L4.375 15.625" stroke="#767E94" stroke-width="1.6"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M15.625 15.625L4.375 4.375" stroke="#767E94" stroke-width="1.6"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        <form method="GET" action="{{ route('frontend.adlist.search') }}" id="adFilterForm">
                            <div class="accordion list-sidebar__accordion" id="accordionGroup">
                                <div class="accordion-item list-sidebar__accordion-item category">
                                    <h2 class="accordion-header list-sidebar__accordion-header" id="category">
                                        <button class="accordion-button list-sidebar__accordion-button" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#categoryCollapse"
                                            aria-expanded="true" aria-controls="categoryCollapse">
                                            {{ __('category') }}
                                        </button>
                                    </h2>
                                    <div id="categoryCollapse" class="accordion-collapse collapse show"
                                        aria-labelledby="category" data-bs-parent="#accordionGroup">
                                        <div class="accordion-body list-sidebar__accordion-body">
                                            <div class="accordion list-sidebar__accordion-inner" id="subcategoryGroup">
                                                @foreach ($categories as $category)
                                                    <div class="accordion-item list-sidebar__accordion-inner-item">
                                                        <h2 class="accordion-header"
                                                            id="{{ Str::slug($category->slug) }}">
                                                            <div class="accordion-button list-sidebar__accordion-inner-button {{ isActiveCategorySidebar($category) ? '' : 'collapsed' }}"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#{{ Str::slug($category->slug) }}Collapse"
                                                                aria-expanded="true"
                                                                aria-controls="{{ Str::slug($category->slug) }}Collapse">
                                                                <span class="list-sidebar__accordion-inner-icon">
                                                                    <i class="{{ $category->icon }}"></i>
                                                                </span>
                                                                {{ $category->name }}
                                                                @if ($category->subcategories->count() > 1)
                                                                    <span class="icon icon--plus">
                                                                        <x-svg.plus-light-icon />
                                                                    </span>
                                                                @endif
                                                                <span class="icon icon--minus">
                                                                    <x-svg.minus-icon />
                                                                </span>
                                                            </div>
                                                        </h2>
                                                        <div id="{{ Str::slug($category->slug) }}Collapse"
                                                            class="accordion-collapse collapse {{ isActiveCategorySidebar($category) ? 'show' : '' }}"
                                                            aria-labelledby="{{ $category->slug }}"
                                                            data-bs-parent="#subcategoryGroup">
                                                            <div class="accordion-body list-sidebar__accordion-inner-body">
                                                                @foreach ($category->subcategories as $subcategory)
                                                                    <div class="list-sidebar__accordion-inner-body--item">
                                                                        <div class="form-check">
                                                                            <input id="{{ $subcategory->slug }}"
                                                                                type="checkbox" name="subcategory[]"
                                                                                value="{{ $subcategory->slug }}"
                                                                                class="form-check-input"
                                                                                {{ request('subcategory') && in_array($subcategory->slug, request('subcategory')) ? 'checked' : '' }}
                                                                                onchange="changeFilter()" />

                                                                            <x-forms.label
                                                                                name="{{ $subcategory->name }}"
                                                                                for="{{ $subcategory->slug }}"
                                                                                class="form-check-label" />
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if (isset($searchable_fields) && count($searchable_fields))
                                    <x-frontend.ad.custom-field :searchableFields="$searchable_fields" />
                                @endif
                                <div class="accordion-item list-sidebar__accordion-item price">
                                    <h2 class="accordion-header list-sidebar__accordion-header" id="priceTag">
                                        <button class="accordion-button list-sidebar__accordion-button collapsed"
                                            type="button" data-bs-toggle="collapse" data-bs-target="#priceCollapse"
                                            aria-expanded="false" aria-controls="priceCollapse">
                                            {{ __('prices') }}
                                        </button>
                                    </h2>
                                    <input type="hidden" name="price_min" id="price_min">
                                    <input type="hidden" name="price_max" id="price_max">
                                    <div id="priceCollapse" class="accordion-collapse collapse show"
                                        aria-labelledby="priceTag" data-bs-parent="#accordionGroup">
                                        <div class="accordion-body list-sidebar__accordion-body">
                                            <div class="price-range-slider">
                                                <div id="priceRangeSlider"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    <div class="ad-list__content row">
                        @forelse ($adlistings as $ad)
                            <x-frontend.single-ad :ad="$ad" :adfields="$ad->productCustomFields" className="col-lg-4 col-md-6" />
                        @empty
                            <x-not-found2 message="{{ __('no_ads_found') }}" />
                        @endforelse
                    </div>
                    <div class="page-navigation">
                        {{ $adlistings->links() }}
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
        function changeFilter() {
            const slider = document.getElementById('priceRangeSlider')
            const value = slider.noUiSlider.get(true);
            document.getElementById('price_min').value = value[0]
            document.getElementById('price_max').value = value[1]
            const form = $('#adFilterForm')
            const data = form.serializeArray();
            $('#adFilterForm').submit()
        }

        function setDefaultPriceRangeValue() {
            const slider = document.getElementById('priceRangeSlider')
            slider.noUiSlider.set([{{ request('price_min') }}, {{ request('price_max') }}]);
        }

        function customformFilter(field, value, type) {
            // // console.log(field, value, type)
            // if (type == 'select') {
            //     $(`#${field}`).val(value)
            // } else {
            //     // $(`input[name=${field}]`).val(value)
            //     // $(`#${field}`).val(value)
            //     console.log(field, value, type)
            //     // console.log($(`input[name=${field}]`).val())
            //     // let radioValue = $(`#${field}`).is(":checked");

            // }

            $('#adFilterForm').submit()
        }

        $(document).ready(function() {
            const slider = document.getElementById('priceRangeSlider')
            let maxRange = Number.parseInt("{{ $adMaxPrice ?? 500 }}")
            let minPrice = 0;
            let maxPrice = maxRange;
            @if (request()->has('price_min') && request()->has('price_max'))
                minPrice = Number.parseInt("{{ request('price_min', 0) }}")
                maxPrice = Number.parseInt("{{ request('price_max', 500) }}")
            @endif
            noUiSlider.create(slider, {
                start: [minPrice, maxPrice],
                connect: true,
                range: {
                    min: [0],
                    max: [maxRange],
                },
                format: wNumb({
                    decimals: 2,
                    thousand: ',',
                    suffix: ' ({{ env('APP_CURRENCY_SYMBOL') }})',
                }),
                tooltips: true,
            });

            slider.noUiSlider.on('change', function() {
                changeFilter();
            });

        });
    </script>
@endsection
