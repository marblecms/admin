@if($value && count($value))
    <ul>
        @foreach($value as $file)
            @if(!empty($file['url']))
            <li>
                <a href="{{ $file['url'] }}" download="{{ $file['original_filename'] ?? '' }}">{{ $file['original_filename'] ?? basename($file['url']) }}</a>
            </li>
            @endif
        @endforeach
    </ul>
@endif
