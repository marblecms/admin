@if($item->blueprint->versionable)
<div class="main-box clearfix profile-box-menu">
    <div class="main-box-body clearfix">
        <div class="profile-box-header gray-bg clearfix">
            <h2>{{ trans('marble::admin.versions') }} @if($revisions->count()) ({{ $revisions->count() }}) @endif</h2>
        </div>
        <div class="profile-box-content clearfix">
            @if($revisions->isEmpty())
                <ul class="menu-items">
                    <li><a href="#" class="marble-meta">{{ trans('marble::admin.no_versions') }}</a></li>
                </ul>
            @else
                <ul class="menu-items">
                    @foreach($revisions as $revision)
                        <form id="revert-{{ $revision->id }}"
                              method="POST"
                              action="{{ route('marble.item.revert', [$item, $revision]) }}"
                              class="marble-hidden">
                            @csrf
                        </form>
                        <li class="menu-item-action">
                            <span>
                                {{ $revision->created_at->format('d.m. H:i') }}
                                @if($revision->user)
                                    <small class="marble-meta marble-mr-xs">{{ $revision->user->name }}</small>
                                @endif
                            </span>
                            <div class="btn-group btn-group-xs">
                                <a href="{{ route('marble.item.diff', [$item, $revision]) }}" class="btn btn-xs btn-default">{{ trans('marble::admin.diff') }}</a>
                                <button type="submit" form="revert-{{ $revision->id }}" class="btn btn-xs btn-info"
                                        onclick="formDirty=false;">{{ trans('marble::admin.restore') }}</button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endif
