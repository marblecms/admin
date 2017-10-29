<div class="form-group">
    <label><b>Konfiguration</b></label>
    
    <div class="row">
        <div class="col-md-2">
            <label>Größe</label>
            <input type="number" name="configuration[{{$classAttribute->id}}][rows]" value="{{$classAttribute->configuration["rows"]}}" class="form-control" />
        </div>
    </div>
</div>