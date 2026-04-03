@if($item->blueprint->schedulable)
<div class="main-box clearfix profile-box-menu">
    <div class="main-box-body clearfix">
        <div class="profile-box-header gray-bg clearfix">
            <h2>{{ trans('marble::admin.scheduling') }}</h2>
        </div>
        <div class="profile-box-content clearfix marble-box-body">
            <form method="POST" action="{{ route('marble.item.schedule', $item) }}" id="scheduling-form">
                @csrf
                <div class="form-group marble-mb-sm">
                    <label class="marble-label-sm marble-mb-0">{{ trans('marble::admin.publish_at') }}</label>
                    <input type="datetime-local" name="published_at" class="form-control input-sm"
                        value="{{ $item->published_at ? $item->published_at->format('Y-m-d\TH:i') : '' }}" />
                </div>
                <div class="form-group marble-mb-sm">
                    <label class="marble-label-sm marble-mb-0">{{ trans('marble::admin.expires_at') }}</label>
                    <input type="datetime-local" name="expires_at" class="form-control input-sm"
                        value="{{ $item->expires_at ? $item->expires_at->format('Y-m-d\TH:i') : '' }}" />
                </div>
                <div class="text-right">
                    <button type="submit" class="btn btn-xs btn-info">{{ trans('marble::admin.save_schedule') }}</button>
                </div>
                <p class="marble-meta marble-mt-xs">{{ trans('marble::admin.scheduling_hint') }}</p>
            </form>
            @if($item->published_at || $item->expires_at)
                <div class="marble-mt-xs marble-meta">
                    @if($item->published_at)
                        <div>{{ trans('marble::admin.publish_at') }}: {{ $item->published_at->format('d.m.Y H:i') }}</div>
                    @endif
                    @if($item->expires_at)
                        <div>{{ trans('marble::admin.expires_at') }}: {{ $item->expires_at->format('d.m.Y H:i') }}</div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endif
