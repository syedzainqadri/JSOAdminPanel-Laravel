@extends('frontend.postad.index')

@section('title')
    {{ __('edit_ad') }} ({{ __('steps02') }}) - {{ $ad->title }}
@endsection

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
            <form id="step2_edit_form" action="{{ route('frontend.post.step2.update', $ad->slug) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
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
                            value="{{ $ad->phone }}" class="@error('phone') border-danger @enderror" />
                    </div>
                    <div class="input-field">
                        <x-forms.label name="backup_phone_number" for="backupPhone" :required="false" />
                        <input name="phone_2" id="backupPhone" type="tel" class="backupPhone"
                            placeholder="{{ __('phone_number') }}" value="{{ $ad->phone_2 }}" />
                    </div>
                </div>
                <div class="input-field__group">
                    <div class="input-field">
                        <x-forms.label name="whatsapp_number" for="backupPhone" :required="false">
                            (<a href="https://faq.whatsapp.com/iphone/how-to-link-to-whatsapp-from-a-different-app/?lang=en"
                                target="_blank">{{ __('get_help') }}</a>)
                        </x-forms.label>
                        <input name="whatsapp" id="backupPhone" type="number" class="backupPhone"
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
                    @foreach ($fields as $field)
                        @if ($field->customField->type == 'text')
                            <div class="col-md-6 input-field__group">
                                <div class="input-field">
                                    <x-forms.label name="{{ $field->customField->name }}" :required="$field->customField->required" />
                                    <input value="{{ $field->value }}" type="text"
                                        name="{{ $field->customField->slug }}"
                                        placeholder="{{ $field->customField->name }}"
                                        class="form-control @error($field->customField->slug) border-danger @enderror" />
                                    @error($field->customField->slug)
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif
                        @if ($field->customField->type == 'select')
                            <div class="col-md-6 input-field__group">
                                <div class="input-select">
                                    <x-forms.label name="{{ $field->customField->name }}" :required="$field->customField->required" />
                                    <select id="select" class="form-control" name="{{ $field->customField->slug }}">
                                        @foreach ($field->customField->values as $value)
                                            <option
                                                {{ ucfirst($field->value) == ucfirst($value->value) ? 'selected' : '' }}
                                                value="{{ $value->value }}">
                                                {{ ucfirst($value->value) }}</option>
                                        @endforeach
                                    </select>
                                    @error($field->customField->slug)
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif
                        @if ($field->customField->type == 'file')
                            <div class="col-md-6 input-field__group">
                                <div class="input-field">
                                    <x-forms.label name="{{ $field->customField->name }}" :required="$field->customField->required" />
                                    <input type="file" name="{{ $field->customField->slug }}"
                                        class="form-control @error($field->customField->slug) is-invalid @enderror custom-file-input"
                                        id="customFile">
                                    @error($field->customField->slug)
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif
                        @if ($field->customField->type == 'textarea')
                            <div class="col-md-6 input-field__group">
                                <div class="input-field">
                                    <x-forms.label name="{{ $field->customField->name }}" :required="$field->customField->required" />
                                    <textarea name="{{ $field->customField->slug }}" placeholder="{{ $field->customField->name }}" cols="12"
                                        rows="2" id="description" class="form-control @error($field->customField->slug) border-danger @enderror ">{{ $field->customField->slug }}</textarea>
                                    @error($field->customField->slug)
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif
                        @if ($field->customField->type == 'radio')
                            <div class="col-md-6">
                                <x-forms.label name="{{ $field->customField->name }}" :required="$field->customField->required" />
                                @foreach ($field->customField->values as $value)
                                    <div class="form-check">
                                        <input {{ ucfirst($field->value) == ucfirst($value->value) ? 'checked' : '' }}
                                            value="{{ ucfirst($value->value) }}"
                                            name="{{ $field->customField->slug }}" type="radio"
                                            class="form-check-input" id="checkme{{ $value->id }}" />
                                        <x-forms.label name="{{ $value->value }}" :required="false"
                                            class="form-check-label" for="checkme{{ $value->id }}" />
                                    </div>
                                @endforeach
                                @error($field->customField->slug)
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif
                        {{-- @if ($field->customField->type == 'url')
                            <div class="col-md-6 input-field__group">
                                <div class="input-field">
                                    <x-forms.label name="{{ $field->customField->name }}" :required="$field->customField->required" />
                                    <textarea name="{{ $field->customField->slug }}" placeholder="{{ $field->customField->name }}" cols="12"
                                        rows="2" id="description" class="form-control @error($field->customField->slug) border-danger @enderror ">{{ $field->customField->slug }}</textarea>
                                    @error($field->customField->slug)
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif --}}
                        @if ($field->customField->type == 'url')
                            <div class="col-sm-6">
                                <div class="input-field">
                                    <x-forms.label name="{{ $field->customField->name }}" :required="$field->customField->required" />
                                    <input type="url" name="{{ $field->customField->slug }}"
                                        class="form-control @error($field->customField->slug) border-danger @enderror"
                                        value="{{ old($field->customField->slug, $field->value) }}"
                                        placeholder="{{ $field->customField->name }}">
                                    @error($field->customField->slug)
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif
                        @if ($field->customField->type == 'number')
                            <div class="col-sm-6">
                                <div class="input-field">
                                    <x-forms.label name="{{ $field->customField->name }}" :required="$field->customField->required" />
                                    <input min="1" type="number" name="{{ $field->customField->slug }}"
                                        class="form-control @error($field->customField->slug) border-danger @enderror"
                                        value="{{ old($field->customField->slug, $field->value) }}"
                                        placeholder="{{ $field->customField->name }}">
                                    @error($field->customField->slug)
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif
                        @if ($field->customField->type == 'date')
                            <div class="col-sm-6">
                                <div class="input-field">
                                    <x-forms.label name="{{ $field->customField->name }}" :required="$field->customField->required" />
                                    <input type="date" name="{{ $field->customField->slug }}"
                                        class="form-control @error($field->customField->slug) border-danger @enderror"
                                        value="{{ old($field->customField->slug, $field->value) }}"
                                        placeholder="{{ $field->customField->name }}">
                                    @error($field->customField->slug)
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif
                        @php
                            $fieldId = 'cf.' . $field->customField->id;
                            $fieldName = 'cf[' . $field->customField->id . ']';
                            $fieldOld = 'cf.' . $field->customField->id;
                        @endphp

                        @if ($field->customField->type == 'checkbox')
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <x-forms.label name="{{ $field->customField->name }}" :required="$field->customField->required" />
                                    <div class="row">
                                        @foreach ($field->customField->values as $value)
                                            @if ($loop->first)
                                                <input type="hidden" value="0" name="{{ $fieldName }}">
                                                <div class="col-md-3 mb-1">
                                                    <div class="icheck-success d-inline">
                                                        <input {{ $field->value ? 'checked' : '' }} value="1"
                                                            name="{{ $fieldName }}" type="checkbox"
                                                            class="form-check-input" id="{{ $fieldId }}" />
                                                        <label class="form-check-label"
                                                            for="{{ $fieldId }}">{{ $value->value }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                    @error($field->customField->slug)
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif
                        @if ($field->customField->type == 'checkbox_multiple')
                            @php
                                $exploded_values = explode(', ', $field->value);
                            @endphp

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <x-forms.label name="{{ $field->customField->name }}" :required="$field->customField->required" />
                                    @foreach ($field->customField->values as $key => $value)
                                        <div class="icheck-success ">
                                            <input id="{{ $fieldId . '.' . $value->id }}"
                                                name="{{ $fieldName . '[' . $value->id . ']' }}" type="checkbox"
                                                value="{{ $value->id }}" class="form-check-input"
                                                {{ in_array($value->id, $exploded_values) ? 'checked' : '' }} />
                                            <label class="form-check-label" for="{{ $fieldId . '.' . $value->id }}">
                                                {{ $value->value }}
                                            </label>
                                        </div>
                                    @endforeach

                                    @error($field->customField->slug)
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="dashboard-post__action-btns">
                    <a href="{{ route('frontend.post.step1.back', $ad->slug) }}" class="btn btn--lg btn--outline">
                        {{ __('previous') }}
                    </a>
                    <button type="button" onclick="updateCancelEdit()" class="btn btn--lg bg-warning text-light">
                        {{ __('update_cancel_edit') }}
                        <span class="icon--right">
                            <x-svg.cross-icon />
                        </span>
                    </button>
                    <button type="submit" class="btn btn--lg">
                        {{ __('update_next_step') }}
                        <span class="icon--right">
                            <x-svg.right-arrow-icon />
                        </span>
                    </button>
                </div>
                <input type="hidden" id="cancel_edit_input" name="cancel_edit" value="0">
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
    <!-- >=>Mapbox<=< -->
    <script src="{{ asset('frontend') }}/js/axios.min.js"></script>
    <script src="{{ asset('frontend/plugins/mapbox/mapbox-gl-geocoder.min.js') }}"></script>
    <script src="{{ asset('frontend/plugins/mapbox/mapbox-gl.js') }}"></script>

    <!--=============== map box ===============-->
    <script>
        var token = "{{ setting('map_box_key') }}";
        mapboxgl.accessToken = token;
        const coordinates = document.getElementById('coordinates');

        var oldlat = {!! $lat ? $lat : setting('default_lat') !!};
        var oldlng = {!! $long ? $long : setting('default_long') !!};

        const map = new mapboxgl.Map({
            container: 'map-box',
            style: 'mapbox://styles/mapbox/streets-v11',
            center: [oldlng, oldlat],
            zoom: 6
        });
        // Add the control to the map.
        map.addControl(
            new MapboxGeocoder({
                accessToken: mapboxgl.accessToken,
                mapboxgl: mapboxgl
            })
        );
        // Add the control to the map.
        const geocoder = new MapboxGeocoder({
            accessToken: mapboxgl.accessToken,
            marker: {
                color: 'orange',
                draggable: true
            },
            mapboxgl: mapboxgl
        });
        var marker = new mapboxgl.Marker({
                draggable: true
            }).setLngLat([oldlng, oldlat])
            .addTo(map);

        function onDragEnd() {
            const lngLat = marker.getLngLat();
            let lat = lngLat.lat;
            let lng = lngLat.lng;

            axios.get(
                    `https://api.mapbox.com/geocoding/v5/mapbox.places/${lng},${lat}.json?&types=address,neighborhood,locality,place,district,postcode,region,country&access_token=${token}`
                )
                .then((res) => {

                    var form = new FormData();
                    form.append('lat', lat);
                    form.append('lng', lng);

                    for (let i = 0; i < res.data.features.length; i++) {
                        form.append(res.data.features[i].place_type[0], res.data.features[i].text);
                    }

                    axios.post(
                            '/set/session', form
                        )
                        .then((res) => {
                            // console.log(res.data);
                            // toastr.success("Location Saved", 'Success!');
                        })
                        .catch((e) => {
                            toastr.error("Something Wrong", 'Error!');
                        });
                })
                .catch((e) => {
                    // toastr.error("Something Wrong", 'Error!');
                });
        }

        function add_marker(event) {
            var coordinates = event.lngLat;
            marker.setLngLat(coordinates).addTo(map);

        }
        map.on('style.load', function() {
            map.on('click', function(e) {
                var coordinates = e.lngLat;
                let lat = parseFloat(coordinates.lat);
                let lng = parseFloat(coordinates.lng);
                axios.get(
                        `https://api.mapbox.com/geocoding/v5/mapbox.places/${lng},${lat}.json?&types=address,neighborhood,locality,place,district,postcode,region,country&access_token=${token}`
                    )
                    .then((res) => {

                        var form = new FormData();
                        form.append('lat', lat);
                        form.append('lng', lng);

                        for (let i = 0; i < res.data.features.length; i++) {
                            form.append(res.data.features[i].place_type[0], res.data.features[i].text);
                        }

                        axios.post(
                                '/set/session', form
                            )
                            .then((res) => {
                                // console.log(res.data);
                                // toastr.success("Location Saved", 'Success!');
                            })
                            .catch((e) => {
                                toastr.error("Something Wrong", 'Error!');
                            });
                    })
                    .catch((e) => {
                        // toastr.error("Something Wrong", 'Error!');
                    });
            });
        });
        map.on('click', add_marker);
        marker.on('dragend', onDragEnd);
    </script>
    <script>
        $('.mapboxgl-ctrl-logo').addClass('d-none');
        $('.mapboxgl-compact').addClass('d-none');
        $('.mapboxgl-ctrl-attrib-inner').addClass('d-none');
    </script>
    <!-- ============== map box ============= -->
    <!-- ============== google map ========= -->
    <script>
        function initMap() {
            var token = "{{ setting('google_map_key') }}";
            var oldlat = {!! $lat ? $lat : setting('default_lat') !!};
            var oldlng = {!! $long ? $long : setting('default_long') !!};

            const map = new google.maps.Map(document.getElementById("google-map"), {
                zoom: 7,
                center: {
                    lat: oldlat,
                    lng: oldlng
                },
            });

            const image =
                "https://gisgeography.com/wp-content/uploads/2018/01/map-marker-3-116x200.png";
            const beachMarker = new google.maps.Marker({

                draggable: true,
                position: {
                    lat: oldlat,
                    lng: oldlng
                },
                map,
                // icon: image
            });

            google.maps.event.addListener(map, 'click',
                function(event) {
                    pos = event.latLng
                    beachMarker.setPosition(pos);
                    let lat = beachMarker.position.lat();
                    let lng = beachMarker.position.lng();
                    axios.post(
                        `https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=${token}`
                    ).then((data) => {
                        const total = data.data.results.length;
                        let amount = '';
                        if (total > 1) {
                            amount = total - 2;
                        }
                        const result = data.data.results.slice(amount);
                        let country = '';
                        let region = '';

                        for (let index = 0; index < result.length; index++) {
                            const element = result[index];


                            if (element.types[0] == 'country') {
                                country = element.formatted_address;
                            }
                            if (element.types[0] == 'administrative_area_level_1') {

                                const str = element.formatted_address;
                                const first = str.split(',').shift()
                                region = first;
                            }
                        }

                        var form = new FormData();
                        form.append('lat', lat);
                        form.append('lng', lng);

                        form.append('country', country);
                        form.append('region', region);

                        axios.post(
                                '/set/session', form
                            )
                            .then((res) => {
                                // console.log(res.data);
                                // toastr.success("Location Saved", 'Success!');
                            })
                            .catch((e) => {
                                toastr.error("Something Wrong", 'Error!');
                            });
                    })
                });

            google.maps.event.addListener(beachMarker, 'dragend',
                function() {
                    let lat = beachMarker.position.lat();
                    let lng = beachMarker.position.lng();
                    axios.post(
                        `https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=${token}`
                    ).then((data) => {
                        const total = data.data.results.length;
                        let amount = '';
                        if (total > 1) {
                            amount = total - 2;
                        }
                        const result = data.data.results.slice(amount);
                        let country = '';
                        let region = '';

                        for (let index = 0; index < result.length; index++) {
                            const element = result[index];


                            if (element.types[0] == 'country') {
                                country = element.formatted_address;
                            }
                            if (element.types[0] == 'administrative_area_level_1') {

                                const str = element.formatted_address;
                                const first = str.split(' ').shift()
                                region = first;
                            }
                        }

                        var form = new FormData();
                        form.append('lat', lat);
                        form.append('lng', lng);

                        form.append('country', country);
                        form.append('region', region);

                        axios.post(
                                '/set/session', form
                            )
                            .then((res) => {
                                // console.log(res.data);
                                // toastr.success("Location Saved", 'Success!');
                            })
                            .catch((e) => {
                                toastr.error("Something Wrong", 'Error!');
                            });
                    })
                });

            // Search
            var input = document.getElementById('searchInput');
            map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

            var autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.bindTo('bounds', map);

            var infowindow = new google.maps.InfoWindow();
            var marker = new google.maps.Marker({
                map: map,
                anchorPoint: new google.maps.Point(0, -29)
            });

            autocomplete.addListener('place_changed', function() {
                infowindow.close();
                marker.setVisible(false);
                var place = autocomplete.getPlace();

                if (place.geometry.viewport) {
                    map.fitBounds(place.geometry.viewport);
                } else {
                    map.setCenter(place.geometry.location);
                    map.setZoom(17);
                }
            });
        }

        window.initMap = initMap;
    </script>
    <script>
        @php
            $link1 = 'https://maps.googleapis.com/maps/api/js?key=';
            $link2 = setting('google_map_key');
            $Link3 = '&callback=initMap&libraries=places,geometry';
            $scr = $link1 . $link2 . $Link3;
        @endphp;
    </script>
    <script src="{{ $scr }}" async defer></script>
    <!-- =============== google map ========= -->
    <script type="text/javascript">
        $(document).ready(function() {
            $("[data-toggle=tooltip]").tooltip()
        })
    </script>
    <!-- >=>Mapbox<=< -->
    <script>
        // ad update and cancel edit
        function updateCancelEdit() {
            $('#cancel_edit_input').val(1)
            $('#step2_edit_form').submit()
        }
    </script>
@endsection
