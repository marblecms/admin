@if($value && !empty($value['url']))
    <a href="{{ $value['url'] }}" download="{{ $value['original_filename'] ?? '' }}">{{ $value['original_filename'] ?? basename($value['url']) }}</a>
@endif
