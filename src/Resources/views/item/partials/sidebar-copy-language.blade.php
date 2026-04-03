@if(count($languages) > 1)
<div class="main-box clearfix profile-box-menu">
    <div class="main-box-body clearfix">
        <div class="profile-box-header gray-bg clearfix">
            <h2>{{ trans('marble::admin.copy_language') }}</h2>
        </div>
        <div class="profile-box-content clearfix marble-box-body">
            <form method="POST" action="{{ route('marble.item.copy-language', $item) }}">
                @csrf
                <div class="marble-flex-center-sm marble-mb-sm">
                    <select name="from_language_id" class="form-control input-sm marble-flex-1">
                        @foreach($languages as $lang)
                            <option value="{{ $lang->id }}">{{ strtoupper($lang->code) }}</option>
                        @endforeach
                    </select>
                    <span class="marble-redirect-arrow">→</span>
                    <select name="to_language_id" class="form-control input-sm marble-flex-1">
                        @foreach($languages as $lang)
                            <option value="{{ $lang->id }}" {{ $loop->index === 1 ? 'selected' : '' }}>{{ strtoupper($lang->code) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-xs btn-default btn-block"
                        onclick="return confirm('{{ trans('marble::admin.copy_language_confirm') }}')">
                    @include('marble::components.famicon', ['name' => 'page_copy']) {{ trans('marble::admin.copy_language') }}
                </button>
            </form>
            <small class="text-muted marble-block marble-mt-xs">{{ trans('marble::admin.copy_language_hint') }}</small>
        </div>
    </div>
</div>
@endif
