@if(is_string($value)){{ $value }}@elseif($value !== null){{ json_encode($value) }}@endif
