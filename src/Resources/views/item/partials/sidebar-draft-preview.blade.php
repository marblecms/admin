@if($item->status !== 'published')
<div class="main-box clearfix profile-box-menu">
    <div class="main-box-body clearfix">
        <div class="profile-box-header gray-bg clearfix">
            <h2>{{ trans('marble::admin.draft_preview') }}</h2>
        </div>
        <div class="profile-box-content clearfix marble-box-body">
            @if(session('preview_url'))
                <div class="marble-mb-sm">
                    <a href="{{ session('preview_url') }}" target="_blank" class="btn btn-xs btn-success btn-block">
                        @include('marble::components.famicon', ['name' => 'monitor']) {{ trans('marble::admin.open_preview') }}
                    </a>
                    <small class="text-muted marble-block marble-mt-xs marble-break-all">{{ session('preview_url') }}</small>
                </div>
            @elseif($item->preview_token)
                @php $frontendUrl = rtrim(config('marble.frontend_url', ''), '/'); @endphp
                <div class="marble-mb-sm">
                    <a href="{{ $frontendUrl }}/marble-preview/{{ $item->preview_token }}" target="_blank" class="btn btn-xs btn-success btn-block">
                        @include('marble::components.famicon', ['name' => 'monitor']) {{ trans('marble::admin.open_preview') }}
                    </a>
                </div>
            @endif
            <form method="POST" action="{{ route('marble.item.preview.generate', $item) }}">
                @csrf
                <button type="submit" class="btn btn-xs btn-default btn-block">
                    @include('marble::components.famicon', ['name' => 'key']) {{ $item->preview_token ? trans('marble::admin.refresh_preview_token') : trans('marble::admin.generate_preview') }}
                </button>
            </form>
            <small class="text-muted marble-block marble-mt-xs">{{ trans('marble::admin.draft_preview_hint') }}</small>
        </div>
    </div>
</div>
@endif
