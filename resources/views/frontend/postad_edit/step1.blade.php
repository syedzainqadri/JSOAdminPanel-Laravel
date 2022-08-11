@extends('frontend.postad.index')

@section('title')
    {{ __('edit_ad') }} ({{ __('steps01') }}) - {{ $ad->title }}
@endsection

@section('post-ad-content')
    <!-- Step 01 -->
    <div class="tab-pane fade show active" id="pills-basic" role="tabpanel" aria-labelledby="pills-basic-tab">
        <div class="dashboard-post__information step-information">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form id="step1_edit_form" action="{{ route('frontend.post.update', $ad->slug) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="dashboard-post__information-form">
                    <div class="input-field__group">
                        <div class="input-field">
                            <x-forms.label name="ad_name" for="adname" required="true" />
                            <input required value="{{ $ad->title }}" name="title" type="text"
                                placeholder="{{ __('ad_name') }}" id="adname"
                                class="@error('title') border-danger @enderror" />
                        </div>
                        <div class="input-field">
                            <x-forms.label name="price" for="price" required="true">
                                ({{ config('zakirsoft.currency_symbol') }})
                            </x-forms.label>
                            <input required value="{{ $ad->price ?? '' }}" name="price" type="number" min="1"
                                placeholder="{{ __('price') }}" id="price"
                                class="@error('price') border-danger @enderror" />
                        </div>
                    </div>
                    <div class="input-field__group">
                        <div class="input-select">
                            <x-forms.label name="category" for="allCategory" required="true" />
                            <select required name="category_id" id="ad_category"
                                class="form-control select-bg @error('category_id') border-danger @enderror">
                                <option value="" hidden>{{ __('select_category') }}</option>
                                @foreach ($categories as $category)
                                    <option {{ $category->id == $ad->category_id ? 'selected' : '' }}
                                        value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input-select">
                            <x-forms.label name="subcategory" for="subcategory" required="true" />
                            <select name="subcategory_id" id="ad_subcategory"
                                class="form-control select-bg @error('subcategory_id') border-danger @enderror">
                                <option value="" selected>{{ __('select_subcategory') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="input-field__group">
                        <div class="input-select">
                            <x-forms.label name="brand" for="brand" required="true" />
                            <select required name="brand_id" id="brandd"
                                class="form-control select-bg @error('brand_id') border-danger @enderror">
                                <option value="" hidden>{{ __('select_brand') }}</option>
                                @foreach ($brands as $brand)
                                    <option {{ $brand->id == $ad->brand_id ? 'selected' : '' }}
                                        value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        @if ($ad->featured)
                            <div class="col-lg-3">
                                <div class="form-check">
                                    <input name="featured" type="hidden" value="0">
                                    <input {{ $ad->featured == 1 ? 'checked' : '' }} value="1" name="featured"
                                        type="checkbox" class="form-check-input" id="featured" />
                                    <x-forms.label name="featured" for="featured" class="form-check-label"
                                        :required="false" />
                                </div>
                            </div>
                        @else
                            <input name="featured" type="hidden" value="0">
                        @endif
                    </div>
                </div>
                <div class="dashboard-post__action-btns">
                    <a href="{{ route('frontend.post.cancel.edit') }}" class="btn btn--lg bg-danger text-light">
                        {{ __('cancel_edit') }}
                        <span class="icon--right">
                            <x-svg.cross-icon />
                        </span>
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

@push('post-ad-scripts')
    <script>
        // ad update and cancel edit
        function updateCancelEdit() {
            $('#cancel_edit_input').val(1)
            $('#step1_edit_form').submit()
        }
    </script>
@endpush
