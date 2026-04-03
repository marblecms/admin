<div class="main-box clearfix profile-box-menu">
    <div class="main-box-body clearfix">
        <div class="profile-box-header gray-bg clearfix">
            <h2>{{ trans('marble::admin.url_aliases') }}</h2>
        </div>
        <div class="profile-box-content clearfix marble-box-body">
            <form method="POST" action="{{ route('marble.item.aliases.save', $item) }}" id="aliases-form">
                @csrf
                <div id="aliases-list">
                    @foreach($aliases as $alias)
                    <div class="alias-row marble-flex-center-sm marble-mb-xs">
                        <input type="hidden" name="aliases[{{ $loop->index }}][id]" value="{{ $alias->id }}" />
                        <input type="text"
                               name="aliases[{{ $loop->index }}][alias]"
                               value="{{ $alias->alias }}"
                               placeholder="/kampagne"
                               class="form-control input-sm marble-flex-1" />
                        <select name="aliases[{{ $loop->index }}][language_id]" class="form-control input-sm marble-lang-select">
                            @foreach($languages as $lang)
                                <option value="{{ $lang->id }}" {{ $alias->language_id == $lang->id ? 'selected' : '' }}>{{ strtoupper($lang->code) }}</option>
                            @endforeach
                        </select>
                        <a href="javascript:;" onclick="this.closest('.alias-row').remove()" class="marble-remove-row">&times;</a>
                    </div>
                    @endforeach
                </div>
                <div class="marble-flex-between marble-mt-xs">
                    <button type="button" id="add-alias-btn" class="btn btn-xs btn-info">
                        {{ trans('marble::admin.add_alias') }}
                    </button>
                    <button type="submit" class="btn btn-xs btn-success">
                        @include('marble::components.famicon', ['name' => 'disk']) {{ trans('marble::admin.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
