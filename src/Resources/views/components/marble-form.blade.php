@php
    $langId = \Marble\Admin\Facades\Marble::primaryLanguageId();
    $systemFields = ['name', 'slug'];
@endphp

@if($successMessage())
    <div class="marble-form-success">{{ $successMessage() }}</div>
@else
    <form action="{{ $submitUrl() }}" method="POST" @if($class) class="{{ $class }}" @endif>
        @csrf

        @foreach($item->blueprint->fields as $field)
            @if(in_array($field->identifier, $systemFields)) @continue @endif
            @php $fieldType = $field->fieldTypeInstance(); @endphp
            @if(!$fieldType->allowInForm()) @continue @endif

            <div class="marble-form-field">
                @if($fieldType->identifier() !== 'checkbox')
                    <label for="field_{{ $field->id }}">{{ $field->name }}</label>
                @endif
                @include($fieldType->formComponent(), ['field' => $field, 'langId' => $langId])
            </div>
        @endforeach

        {{ $slot }}

        @if(!isset($hideSubmit) || !$hideSubmit)
            <button type="submit" class="{{ $submitClass ?? '' }}">{{ $submitLabel ?? 'Submit' }}</button>
        @endif
    </form>
@endif
