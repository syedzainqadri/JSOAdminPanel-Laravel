@props(['ad', 'product_custom_field_groups'])

<div>
    @foreach ($productCustomFieldGroups as $key => $customFieldGroup)
        <h2 class="text--body-1">{{ $customFieldGroup[0]->customField->customFieldGroup->name }}</h2>
        <ul class="overview-details">
            @foreach ($customFieldGroup as $field)
                @if ($field->value)
                    @if ($field->customField->type == 'file')
                        <li class="overview-details__item">
                            <span class="text--body-3 title">
                                <i class="{{ $field->customField->icon }} text-info"></i>
                                {{ $field->customField->name }}:
                            </span>
                            <span class="text--body-3 info">
                                <a href="javascript:void(0)" onclick="$('#image-download-form').submit()"
                                    class="download-attachment">{{ __('download') }}</a>
                            </span>
                            <form class="d-none" id="image-download-form"
                                action="{{ route('frontend.attachment.download') }}" method="POST">
                                @csrf
                                <input type="hidden" name="field" value="{{ $field->id }}">
                            </form>
                        </li>
                    @elseif ($field->customField->type == 'url')
                        <li class="overview-details__item">
                            <span class="text--body-3 title">
                                <i class="{{ $field->customField->icon }} text-info"></i>
                                {{ $field->customField->name }}:
                            </span>
                            <span class="text--body-3 info text-lowercase">
                                <a href="{{ $field->value }}">{{ $field->value }}</a>
                            </span>
                        </li>
                    @elseif ($field->customField->type == 'date')
                        <li class="overview-details__item">
                            <span class="text--body-3 title">
                                <i class="{{ $field->customField->icon }} text-info"></i>
                                {{ $field->customField->name }}:
                            </span>
                            <span class="text--body-3 info">
                                {{ formatDate($field->value, 'd M, Y') }}
                            </span>
                        </li>
                    @elseif ($field->customField->type == 'checkbox')
                        <li class="overview-details__item">
                            <span class="text--body-3 title">
                                <i class="{{ $field->customField->icon }} text-info"></i>
                                {{ $field->customField->name }}:
                            </span>
                            <span class="text--body-3 info">
                                @foreach ($field->customField->values as $value)
                                    @if ($loop->first)
                                        {{ $value->value }}
                                    @endif
                                @endforeach
                            </span>
                        </li>
                    @elseif ($field->customField->type == 'checkbox_multiple')
                        <li class="overview-details__item">
                            <span class="text--body-3 title">
                                <i class="{{ $field->customField->icon }} text-info"></i>
                                {{ $field->customField->name }}:
                            </span>
                            <span class="text--body-3 info">
                                @foreach ($field->customField->values as $value)
                                    {{ $value->value }}

                                    @if (!$loop->last)
                                        ,
                                    @endif
                                @endforeach
                            </span>
                        </li>
                    @else
                        <li class="overview-details__item">
                            <span class="text--body-3 title">
                                <i class="{{ $field->customField->icon }} text-info"></i>
                                {{ $field->customField->name }}:
                            </span>
                            <span class="text--body-3 info">{{ $field->value }}</span>
                        </li>
                    @endif
                @endif
            @endforeach
        </ul>
    @endforeach
</div>

@push('component_style')
    <style>
        .download-attachment {
            text-decoration: underline !important;
        }
    </style>
@endpush
