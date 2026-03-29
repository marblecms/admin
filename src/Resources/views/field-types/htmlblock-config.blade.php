@php $rows = $field->configuration['rows'] ?? 10; @endphp

<div class="form-group">
    <label><b>Configuration</b></label>
    <div class="row">
        <div class="col-md-2">
            <label>Rows</label>
            <input type="number" name="configuration[{{ $field->id }}][rows]" value="{{ $rows }}" class="form-control" />
        </div>
    </div>
</div>
