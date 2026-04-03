@if($usedBy->isNotEmpty())
<div class="main-box clearfix profile-box-menu">
    <div class="main-box-body clearfix">
        <div class="profile-box-header gray-bg clearfix">
            <h2>{{ trans('marble::admin.used_by') }} ({{ $usedBy->count() }})</h2>
        </div>
        <div class="profile-box-content clearfix">
            <ul class="menu-items">
                @foreach($usedBy as $ref)
                    <li>
                        <a href="{{ route('marble.item.edit', $ref) }}" class="clearfix">
                            @if($ref->blueprint && $ref->blueprint->icon)
                                @include('marble::components.famicon', ['name' => $ref->blueprint->icon])
                            @endif
                            {{ $ref->name() }}
                            <small class="marble-meta">{{ $ref->blueprint->name ?? '' }}</small>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endif
