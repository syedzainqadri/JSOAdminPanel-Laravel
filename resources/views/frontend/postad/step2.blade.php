@extends('frontend.postad.index')

@section('title', __('step2'))

@section('post-ad-content')
    <!-- Step 02 -->
    <div class="tab-pane fade show active" id="pills-post" role="tabpanel" aria-labelledby="pills-post-tab">
        <div class="dashboard-post__ads step-information">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('frontend.post.step2.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" class="d-none" name="category" value="{{ $category->id }}">

                <div class="input-field__group">
                    <div class="input-field">
                        <input type="hidden" name="show_phone" id="show_phone" value="1">
                        <label for="phone_number">{{ __('phone_number') }}
                            <span>(
                                <input type="checkbox" name="show_phone" id="show_phone_number" value="0"
                                    {{ !$ad->show_phone ? 'checked' : '' ?? '' }}> <label
                                    for="show_phone_number">{{ __('hide_in_details') }}</label>
                                )</span>
                        </label>
                        <input name="phone" id="phoneNumber" type="tel" placeholder="{{ __('phone') }}"
                            value="{{ $ad->phone ?? '' }}" class="@error('phone') border-danger @enderror" />
                    </div>
                    <div class="input-field">
                        <x-forms.label name="backup_phone_number" for="backupPhone" :required="false" />
                        <input name="phone_2" id="backupPhone" type="tel" class="backupPhone"
                            placeholder="{{ __('phone_number') }}" value="{{ $ad->phone_2 ?? '' }}" />
                    </div>
                </div>
                <div class="input-field__group">
                    <div class="input-field">
                        <x-forms.label name="whatsapp_number" for="whatsapp_url" :required="false">
                            (<a href="https://faq.whatsapp.com/iphone/how-to-link-to-whatsapp-from-a-different-app/?lang=en"
                                target="_blank">{{ __('get_help') }}</a>)
                        </x-forms.label>
                        <input name="whatsapp" id="whatsapp_url" type="number" class="backupPhone"
                            placeholder="E.g: 1687******" value="{{ $ad->whatsapp ?? '' }}" />
                    </div>
                </div>
                <div class="row dashboard-post__information-form">
                    <div class="col-md-12 input-field__group">
                        <div class="col-md-12 mb-3">
                            <x-forms.label name="location" required="true" />
                            <span data-toggle="tooltip" title="Drag the pointer Or click your location"
                                data-original-title="Drag the pointer Or click your location">
                                <x-svg.exclamation />
                            </span>
                            @php
                                $map = setting('default_map');
                            @endphp
                            @if ($map == 'map-box')
                                <div class="map mymap" id='map-box'></div>
                            @endif
                            @if ($map == 'google-map')
                                <div>
                                    <input id="searchInput" class="mapClass" type="text"
                                        placeholder="{{ __('enter_a_location') }}">
                                    <div class="map mymap" id="google-map"></div>
                                </div>
                            @endif
                            @error('location')
                                <span class="text-md text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                </div>
                <h5 class="mt-3">
                    {{ __('custom_fields') }}
                </h5>
                <hr>
                <div class="row dashboard-post__information-form">
                    @foreach ($category->customFields as $field)
                        @if ($field->type == 'text')
                            <div class="col-md-6 input-field__group">
                                <div class="input-field">
                                    <x-forms.label name="{{ $field->name }}" for="" :required="$field->required" />
                                    <input value="{{ old($field->name) }}" type="text" name="{{ $field->slug }}"
                                        placeholder="{{ $field->name }}"
                                        class="form-control @error($field->slug) border-danger @enderror" />
                                    @error($field->slug)
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif
                        @if ($field->type == 'select')
                            <div class="col-md-6 input-field__group">
                                <div class="input-select">
                                    <x-forms.label name="{{ $field->name }}" for="select" :required="$field->required" />
                                    <select id="select" class="form-control" name="{{ $field->slug }}">
                                        @foreach ($field->values as $value)
                                            <option
                                                {{ (old(ucfirst($field->value)) == ucfirst($value->value) ? 'selected' : $value->id == 1) ? 'selected' : '' }}
                                                value="{{ $value->value }}">
                                                {{ ucfirst($value->value) }}</option>
                                        @endforeach
                                    </select>
                                    @error($field->slug)
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif
                        @if ($field->type == 'file')
                            <div class="col-md-6 input-field__group">
                                <div class="input-field">
                                    <x-forms.label name="{{ $field->name }}" :required="$field->required" />
                                    <input type="file" name="{{ $field->slug }}"
                                        class="form-control @error($field->slug) is-invalid @enderror custom-file-input"
                                        id="customFile">
                                    @error($field->slug)
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif
                        @if ($field->type == 'textarea')
                            <div class="col-md-6 input-field__group">
                                <div class="input-field">
                                    <x-forms.label name="{{ $field->name }}" :required="$field->required" />
                                    <textarea name="{{ $field->slug }}" placeholder="{{ $field->name }}" cols="12" rows="2"
                                        id="description" class="form-control @error($field->slug) border-danger @enderror ">{{ old($field->slug) }}</textarea>
                                    @error($field->slug)
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif
                        @if ($field->type == 'radio')
                            <div class="col-md-6">
                                <x-forms.label name="{{ $field->name }}" :required="$field->required" />
                                @foreach ($field->values as $value)
                                    <div class="form-check">
                                        <input
                                            {{ old(ucfirst($field->value)) == ucfirst($value->value) ? 'checked' : '' }}
                                            value="{{ ucfirst($value->value) }}" name="{{ $field->slug }}"
                                            type="radio" class="form-check-input" id="checkme{{ $value->id }}" />
                                        <x-forms.label name="{{ $value->value }}" :required="false"
                                            class="form-check-label" for="checkme{{ $value->id }}" />
                                    </div>
                                @endforeach
                                @error($field->slug)
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        @if ($field->type == 'url')
                            <div class="col-sm-6">
                                <div class="input-field">
                                    <x-forms.label name="{{ $field->name }}" :required="$field->required" />
                                    <input type="url" name="{{ $field->slug }}"
                                        class="form-control @error($field->slug) border-danger @enderror"
                                        value="{{ old($field->slug) }}" placeholder="{{ $field->name }}">
                                    @error($field->slug)
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif
                        @if ($field->type == 'number')
                            <div class="col-sm-6">
                                <div class="input-field">
                                    <x-forms.label name="{{ $field->name }}" :required="$field->required" />
                                    <input min="1" type="number" name="{{ $field->slug }}"
                                        class="form-control @error($field->slug) border-danger @enderror"
                                        value="{{ old($field->slug) }}" placeholder="{{ $field->name }}">
                                    @error($field->slug)
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif
                        @if ($field->type == 'date')
                            <div class="col-sm-6">
                                <div class="input-field">
                                    <x-forms.label name="{{ $field->name }}" :required="$field->required" />
                                    <input type="date" name="{{ $field->slug }}"
                                        class="form-control @error($field->slug) border-danger @enderror"
                                        value="{{ old($field->slug) }}" placeholder="{{ $field->name }}">
                                    @error($field->slug)
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif

                        @php
                            $fieldId = 'cf.' . $field->id;
                            $fieldName = 'cf[' . $field->id . ']';
                            $fieldOld = 'cf.' . $field->id;
                            $defaultValue = isset($oldInput) && isset($oldInput[$field->id]) ? $oldInput[$field->id] : '';
                        @endphp
                        @if ($field->type == 'checkbox')
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <x-forms.label name="{{ $field->name }}" :required="$field->required" />
                                    <div class="row">
                                        @foreach ($field->values as $value)
                                            @if ($loop->first)
                                                <input type="hidden" value="0" name="{{ $fieldName }}">
                                                <div class="icheck-success d-inline">
                                                    <input {{ $defaultValue == '1' ? 'checked' : '' }} value="1"
                                                        name="{{ $fieldName }}" type="checkbox"
                                                        class="form-check-input" id="{{ $fieldId }}" />
                                                    <label class="form-check-label"
                                                        for="{{ $fieldId }}">{{ $value->value }}
                                                    </label>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                    @error($field->slug)
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif
                        @if ($field->type == 'checkbox_multiple')
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <x-forms.label name="{{ $field->name }}" :required="$field->required" />
                                    @foreach ($field->values as $value)
                                        <div class="icheck-success">
                                            <input id="{{ $fieldId . '.' . $value->id }}"
                                                name="{{ $fieldName . '[' . $value->id . ']' }}" type="checkbox"
                                                value="{{ $value->id }}" class="form-check-input"
                                                {{ $defaultValue == $value->id ? 'checked' : '' }} />
                                            <label class="form-check-label"
                                                for="{{ $fieldId . '.' . $value->id }}">{{ $value->value }}
                                            </label>
                                        </div>
                                    @endforeach

                                    @error($field->slug)
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="dashboard-post__action-btns">
                    <a href="{{ route('frontend.post.step1.back') }}" class="btn btn--lg btn--outline">
                        {{ __('previous') }}
                    </a>
                    <button type="submit" class="btn btn--lg">
                        {{ __('next_step') }}
                        <span class="icon--right">
                            <x-svg.right-arrow-icon />
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('adlisting_style')
    <!-- >=>Mapbox<=< -->
    <link rel="stylesheet" href="{{ asset('frontend/plugins/mapbox/mapbox-gl-geocoder.css') }}" type="text/css">
    <link href="{{ asset('frontend/plugins/mapbox/mapbox-gl.css') }}" rel="stylesheet">
    <style>
        .mymap {
            width: 100%;
            min-height: 300px;
            border-radius: 12px;
        }

        .p-half {
            padding: 1px;
        }

        .mapClass {
            border: 1px solid transparent;
            margin-top: 15px;
            border-radius: 4px 0 0 4px;
            box-sizing: border-box;
            -moz-box-sizing: border-box;
            height: 35px;
            outline: none;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        }

        #searchInput {
            font-family: 'Roboto';
            background-color: #fff;
            font-size: 16px;
            text-overflow: ellipsis;
            margin-left: 16px;
            font-weight: 400;
            width: 30%;
            padding: 0 11px 0 13px;
        }

        #searchInput:focus {
            border-color: #4d90fe;
        }
    </style>
    <!-- >=>Mapbox<=< -->
@endsection
@section('frontend_script')
    <x-set-mapbox />
    <x-set-googlemap />
@endsection
