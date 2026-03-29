@php $items = $children(); @endphp
@if($slot->isEmpty())
    @foreach($items as $child)
        <div data-marble-id="{{ $child->id }}" data-blueprint="{{ $child->blueprint->identifier }}">
            {{ $child->name() }}
        </div>
    @endforeach
@else
    {{ $slot }}
@endif
