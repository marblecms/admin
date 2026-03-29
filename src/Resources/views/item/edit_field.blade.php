@php
    $fieldType = $field->fieldTypeInstance();
    $primaryLanguageId = \Marble\Admin\Facades\Marble::primaryLanguageId();
@endphp

@foreach($fieldType->getJavascripts() as $javascript)
    <script>Attributes.addFile("{{ $javascript }}");</script>
@endforeach

<div class="form-group" data-field-identifier="{{ $field->identifier }}">
    <label>{{ $field->name }}</label>

    @if($field->translatable)
        <div class="lang-container">
            <div class="lang-switch-container">
                @foreach($languages as $language)
                    <div class="lang-switch {{ $language->id == $primaryLanguageId ? 'active' : '' }}" data-lang="{{ $language->id }}">{{ $language->name }}</div>
                @endforeach
            </div>

            @foreach($languages as $language)
                <div class="lang-content {{ $language->id == $primaryLanguageId ? 'active' : '' }}" data-lang="{{ $language->id }}">
                    @include('marble::field-types.' . $fieldType->identifier(), [
                        'field' => $field,
                        'item' => $item,
                        'languageId' => $language->id,
                        'value' => $item->rawValue($field->identifier, $language->id),
                        'fieldType' => $fieldType,
                    ])
                </div>
            @endforeach
        </div>
    @else
        @include('marble::field-types.' . $fieldType->identifier(), [
            'field' => $field,
            'item' => $item,
            'languageId' => $primaryLanguageId,
            'value' => $item->rawValue($field->identifier, $primaryLanguageId),
            'fieldType' => $fieldType,
        ])
    @endif
</div>
