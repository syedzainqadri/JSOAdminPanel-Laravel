<div class="{{ $className }}">
    <div class="cards cards--one {{ $ad->featured ? 'cards--highlight' : '' }}">
        <a href="{{ route('frontend.addetails', $ad->slug) }}" class="cards__img-wrapper">
            @if ($ad->thumbnail)
                <img src="{{ asset($ad->thumbnail) }}" alt="card-img" class="img-fluid" />
            @else
                <img src="{{ asset('backend/image/default-ad.png') }}" alt="card-img" class="img-fluid" />
            @endif
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
