<div class="form-group">
    @if($attribute->classAttribute->showName)
        <label>{{ $attribute->classAttribute->name }}</label>
    @endif
    @if($attribute->classAttribute->translate)
        <div class="lang-container">
            <div class="lang-switch-container">
                @foreach($languages as $i => $language)
                    <div class="lang-switch {{ $language->id == Config::get("app.locale") ? 'active' : '' }}" data-lang="{{$language->id}}">{{$language->name}}</div>
                @endforeach
            </div>

            @foreach($languages as $i => $language)
                <div class="lang-content {{ $language->id == Config::get("app.locale") ? 'active' : '' }}" data-lang="{{$language->id}}">
                    {!! $attribute->class->renderEdit($language->id)!!} 
                </div>
            @endforeach
        </div>
    @else
        {!! $attribute->class->renderEdit(Config::get("app.locale"))!!}
    @endif
    
    @foreach($attribute->classAttribute->class->getJavascripts() as $javascript)
        <script>
            Attributes.addFile("{{$javascript}}");
        </script>
    @endforeach
    
</div>